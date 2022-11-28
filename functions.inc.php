<?php
$skipJSsettings = true;
include_once "/opt/fpp/www/common.php";
$pluginName = basename(dirname(__FILE__)); 
$logFile = $settings['logDirectory']."/".$pluginName.".log";
$pluginConfigFile = $settings['configDirectory'] . "/plugin." . $pluginName;

if (file_exists($pluginConfigFile)) {
  $pluginSettings = parse_ini_file ($pluginConfigFile);
} else {	
	$pluginSettings="";
	logEntry("No pluginConfigFile");
}


if(isset($_POST['action']) && !empty($_POST['action'])) {
    $action = $_POST['action'];
    switch($action) {
        case 'updateNFLTeam' : updateTeam("football", "nfl");
			break;
		case 'updateNCAATeam' : updateTeam("football", "ncaa");
			break;
		case 'updateNHLTeam' : updateTeam("hockey", "nhl");
			break;
		case 'updateMLBTeam' : updateTeam("baseball", "mlb");
			break;
        case 'blah' : blah();
			break;        
    }
}

function getTeams($sport='football', $league='nfl'){
	$url = "http://site.api.espn.com/apis/site/v2/sports/${sport}/${league}/teams";
	$options = array(
	'http' => array(
		'method'  => 'GET',
		)
	);
	$context = stream_context_create( $options );
	$result = file_get_contents( $url, false, $context );
	$result = json_decode($result, true);
	$teams = $result['sports']['0']['leagues']['0']['teams'];
	$teamNames["No team"]="";
	foreach ($teams as $team) {
		$team = $team['team'];
        $teamNames[$team['displayName']] = $team['id'];		
	}	
	return $teamNames;
}

function getNCAATeams(){
	$url = "http://site.api.espn.com/apis/v2/sports/football/college-football/standings";
	$options = array(
  		'http' => array(
    		'method'  => 'GET',
    	)
	);
	$context = stream_context_create( $options );
	$result = file_get_contents( $url, false, $context );
	$result = json_decode($result, true);
	$teams = array();
	$conferences = $result['children'];
	foreach ($conferences as $conference) {
		if (array_key_exists("children", $conference)) {
			foreach($conference['children'] as $subConference) {
				foreach ($subConference['standings']['entries'] as $team) {
					$teamNames[$team['team']['displayName']] = $team['team']['id'];
				}
			}
		} else {
			foreach ($conference['standings']['entries'] as $team) {
				$teamNames[$team['team']['displayName']] = $team['team']['id'];
			}
		}	
	}
	ksort($teamNames);
	$teamNames = array('No team' => "") + $teamNames;
	return $teamNames;
}

function getSequences(){
	$url = "http://127.0.0.1/api/sequence/";
	$options = array(
		'http' => array(
		'method'  => 'GET',
		)
	);
	$context = stream_context_create( $options );
	$result = file_get_contents( $url, false, $context );
	$sequences = json_decode($result, true);
	$sequenceList["No Sequence"]="";
	foreach ($sequences as $sequence) {		
        $sequenceList[$sequence]=$sequence;		
	}		
	return $sequenceList;
}

function getTeamInfo($sport, $league, $team){
	if ($league == "ncaa") {
		$league = "college-football";
	}
	$url = "http://site.api.espn.com/apis/site/v2/sports/{$sport}/{$league}/teams/{$team}";
	$options = array(
  		'http' => array(
    		'method'  => 'GET',
    	)
	);
	$context = stream_context_create( $options );
	$result = file_get_contents( $url, false, $context );
	$result = json_decode($result, true);

	$teamInfo["logo"] = $result['team']['logos'][0]['href'];
	$teamInfo["abbreviation"] = $result['team']['abbreviation'];
	$teamInfo["name"] = $result['team']['displayName'];
	$teamInfo["nextEventID"] = $result['team']['nextEvent'][0]['id'];
	$teamInfo["nextEventDate"] = $result['team']['nextEvent'][0]['date'];
	$teamInfo["nextEventStatus"] = $result['team']['nextEvent'][0]['competitions'][0]['status']['type']['state'];
	return $teamInfo;

}

function updateTeam($sport, $league){
	logEntry("Updating {$league} Team and logo");
	global $pluginName;
	global $pluginSettings;

	//clear old scores
	WriteSettingToFile("{$league}MyScore",0,$pluginName);
	WriteSettingToFile("{$league}OppoScore",0,$pluginName);

	//configure variables
	if (strlen(urldecode($pluginSettings["{$league}TeamID"]))>0){
		$teamID=urldecode($pluginSettings["{$league}TeamID"]);
		$teamInfo = getTeamInfo($sport, $league, $teamID);
		$teamLogo = $teamInfo['logo'];
		$teamAbbreviation = $teamInfo['abbreviation'];
		$teamName = $teamInfo['name'];
		$teamNextEventID = $teamInfo['nextEventID'];
		$teamNextEventDate = $teamInfo['nextEventDate'];
	}else{
		$teamLogo = "";
		$teamAbbreviation = "";
		$teamName = "";
		$teamNextEventID = "";
		$teamNextEventDate = "";
	}
	WriteSettingToFile("{$league}TeamLogo",$teamLogo,$pluginName);
	WriteSettingToFile("{$league}TeamAbbreviation",$teamAbbreviation,$pluginName);
	WriteSettingToFile("{$league}TeamName",$teamName,$pluginName);
	WriteSettingToFile("{$league}TeamNextEventID",$teamNextEventID,$pluginName);
	WriteSettingToFile("{$league}Start",$teamNextEventDate,$pluginName);
	WriteSettingToFile("{$league}GameStatus","",$pluginName);

	logEntry("{$league} Logo updated " . $teamLogo);
	logEntry("{$league} Abbreviation updated " . $teamAbbreviation);
	logEntry("{$league} Name updated " . $teamName);
	logEntry("{$league} Next game updated " . $teamNextEventDate);
	updateTeamStatus(true);
	return $teamLogo;

}

function getGameStatus($sport, $league, $gameID, $teamID) {
	if ($league == "ncaa") {
		$league = "college-football";
	}

	$url = "http://site.api.espn.com/apis/site/v2/sports/{$sport}/{$league}/scoreboard/{$gameID}";
	$options = array(
	'http' => array(
		'method'  => 'GET',
		)
	);
	
	$context = stream_context_create( $options );
	$game = file_get_contents( $url, false, $context );
	$game = json_decode($game, true);

	//get game info
	$gameStatus['start'] = $game['date'];
	$gameStatus['state'] = $game['status']['type']['state'];

	//check opponent ID
	if ($game['competitions'][0]['competitors'][0]['team']['id'] == $teamID) {
		$teamIndex = 0;
		$oppoIndex = 1;
	} else {
		$teamIndex = 1;
		$oppoIndex = 0;
	}

	//get competitor info
	$gameStatus['oppoID'] = $game['competitions'][0]['competitors'][$oppoIndex]['team']['id'];
	$gameStatus['oppoAbbreviation'] = $game['competitions'][0]['competitors'][$oppoIndex]['team']['abbreviation'];
	$gameStatus['oppoName'] = $game['competitions'][0]['competitors'][$oppoIndex]['team']['displayName'];

	//get score
	$gameStatus['myScore'] = $game['competitions'][0]['competitors'][$teamIndex]['score'];
	$gameStatus['oppoScore'] = $game['competitions'][0]['competitors'][$oppoIndex]['score'];

	return $gameStatus;

}

function updateTeamStatus($reparseSettings=false){
	//initialize globals
	global $logFile;	
	global $pluginConfigFile;
	global $pluginName;
	global $pluginSettings; 

	//reparse settings file - needs to reread team group id on change
	if ($reparseSettings) {
		logEntry("Reparsing config file");
		if (file_exists($pluginConfigFile)) {
			$pluginSettings = parse_ini_file ($pluginConfigFile);
		  } else {	
			  $pluginSettings="";
			  logEntry("No pluginConfigFile");
		  }
	}

	//setup log level
	if (strlen(urldecode($pluginSettings['logLevel']))>0){
		$logLevel=urldecode($pluginSettings['logLevel']);
	}else{
		$logLevel=0;
	}
	
	//get active leagues
	foreach (array('nfl', 'ncaa', 'nhl', 'mlb') as $league) {

		if (strlen(urldecode($pluginSettings["{$league}TeamID"]))>0){
			${$league . "TeamID"}=urldecode($pluginSettings["{$league}TeamID"]);
		} else {
			${$league . "TeamID"}="";
		}

		//initialize sleep times
		${$league . "SleepTime"} = 600;

	}
	$activeLeagues = array();
	if ($nflTeamID != '') {
		array_push($activeLeagues, 'nfl');
	}
	if ($ncaaTeamID != '') {
		array_push($activeLeagues, 'ncaa');
	}
	if ($nhlTeamID != '') {
		array_push($activeLeagues, 'nhl');
	}
	if ($mlbTeamID != '') {
		array_push($activeLeagues, 'mlb');
	}

	//cycle through each league
	foreach ($activeLeagues as $league) {
		logEntry("Parsing league {$league}");	
 
		if (strlen(urldecode($pluginSettings["{$league}GameStatus"]))>1){
			${$league . "GameStatus"}=urldecode($pluginSettings["{$league}GameStatus"]);
		} else {
			${$league . "GameStatus"}="";
		}
		if (strlen(urldecode($pluginSettings["{$league}TeamNextEventID"]))>1){
			${$league . "TeamNextEventID"}=urldecode($pluginSettings["{$league}TeamNextEventID"]);
		} else {
			${$league . "TeamNextEventID"}="";
		}
		if (strlen(urldecode($pluginSettings["{$league}Start"]))>1){
			${$league . "Start"}=urldecode($pluginSettings["{$league}Start"]);
		} else {
			${$league . "Start"}="";
		}
		if (strlen(urldecode($pluginSettings["{$league}MyScore"]))>1){
			${$league . "MyScore"}=urldecode($pluginSettings["{$league}MyScore"]);
		} else {
			${$league . "MyScore"}="0";
		}
		if (strlen(urldecode($pluginSettings["{$league}OppoScore"]))>1){
			${$league . "OppoScore"}=urldecode($pluginSettings["{$league}OppoScore"]);
		} else {
			${$league . "OppoScore"}="0";
		}
		if (strlen(urldecode($pluginSettings["{$league}OppoID"]))>1){
			${$league . "OppoID"}=urldecode($pluginSettings["{$league}OppoID"]);
		} else {
			${$league . "OppoID"}="";
		}
		if (strlen(urldecode($pluginSettings["{$league}WinSequence"]))>1){
			${$league . "WinSequence"}=urldecode($pluginSettings["{$league}WinSequence"]);
		} else {
			${$league . "WinSequence"}="";
		}
	
		if ($league == "nfl" || $league == "ncaa") {
	
			if (strlen(urldecode($pluginSettings["{$league}TouchdownSequence"]))>1){
				${$league . "TouchdownSequence"}=urldecode($pluginSettings["{$league}TouchdownSequence"]);
			} else {
				${$league . "TouchdownSequence"}="";
			}
			if (strlen(urldecode($pluginSettings["{$league}FieldgoalSequence"]))>1){
				${$league . "FieldgoalSequence"}=urldecode($pluginSettings["{$league}FieldgoalSequence"]);
			} else {
				${$league . "FieldgoalSequence"}="";
			}

			$sport = "football";
	
		} elseif ($league == "nhl" || $league == "mlb") {
	
			if (strlen(urldecode($pluginSettings["{$league}ScoreSequence"]))>1){
				${$league . "ScoreSequence"}=urldecode($pluginSettings["{$league}ScoreSequence"]);
			} else {
				${$league . "ScoreSequence"}="";
			}

			switch ($league) {
				case 'nhl' : $sport = "hockey";
					break; 
				case 'mlb' : $sport = "baseball";
			}
	
		}

		//run game checks based on prior game status
		switch (${$league . "GameStatus"}) {
			case "pre":

				$now = new DateTime();
				$gameDate = new DateTime(${$league . "Start"});
				$timeToGame = $gameDate->getTimestamp() - $now->getTimestamp();
				if ($timeToGame < 1200) {
					${$league . "SleepTime"} = 30;
				}

				break;

			case "post":

				//check for next game
				$newInfo = getTeamInfo($sport, $league, ${$league . "TeamID"});
				if ($newInfo['nextEventID'] != ${$league . "TeamNextEventID"}) {
					WriteSettingToFile("{$league}TeamNextEventID",$newInfo['nextEventID'],$pluginName);
					WriteSettingToFile("{$league}Start",$newInfo['nextEventDate'],$pluginName);
					logEntry("{$league} Next game updated " . $newInfo['nextEventDate']);
					//clear old scores
					WriteSettingToFile("{$league}MyScore",0,$pluginName);
					WriteSettingToFile("{$league}OppoScore",0,$pluginName);
					updateTeamStatus(true);
				}

				${$league . "SleepTime"} = 600;

				break;

			default:

				//log polling
				if ($logLevel >= 5) {
					logEntry("Polling ESPN {$league} API");
					echo "Polling ESPN {$league} API";
				}

				//get game status
				$status = getGameStatus($sport, $league, ${$league . "TeamNextEventID"}, ${$league . "TeamID"});

				// set opponent ID
				if ($nflOppoID != $status['oppoID']) {
					WriteSettingToFile("{$league}OppoID",$status['oppoID'],$pluginName);
					WriteSettingToFile("{$league}OppoName",$status['oppoName'],$pluginName);
					WriteSettingToFile("{$league}OppoAbbreviation",$status['oppoAbbreviation'],$pluginName);
				}

				//check score changes
				if ($sport == "football") {

					if (${$league . "MyScore"} + 6 == $status['myScore']) {
						//play touchdown sequence if set
						if (${$league . "TouchdownSequence"} != 'none') {
							insertPlaylistImmediate(${$league . "TouchdownSequence"});
							logEntry("{$league} Touchdown! Playing sequence.");					
						} else {
							logEntry("{$league} Touchdown Triggered but no sequence selected");
						}
					} elseif (${$league . "MyScore"} + 3 == $status['myScore']) {
						//play fieldgoal sequence if set
						if (${$league . "FieldgoalSequence"} != 'none') {
							insertPlaylistImmediate(${$league . "FieldgoalSequence"});
							logEntry("{$league} Fieldgoal! Playing sequence.");					
						} else {
							logEntry("{$league} Fieldgoal Triggered but no sequence selected");
						}
					}

				} elseif ($sport == "hockey" || $sport == "baseball") {
					
					if (${$league . "MyScore"} < $status['myScore']) {
						//play score sequence if set
						if (${$league . "ScoreSequence"} != 'none') {
							insertPlaylistImmediate(${$league . "ScoreSequence"});
							logEntry("{$league} Score! Playing sequence.");					
						} else {
							logEntry("{$league} Score Triggered but no sequence selected");
						}
					}	
				}

				//update stored scores
				if (${$league . "MyScore"} != $status['myScore']) {
					WriteSettingToFile("{$league}MyScore",$status['myScore'],$pluginName);
				}
				if (${$league . "OppoScore"} != $status['oppoScore']) {
					WriteSettingToFile("{$league}OppoScore",$status['oppoScore'],$pluginName);
				}

				//update sleep timer
				switch ($status['state']){
					case "in":						
						${$league . "SleepTime"} = 5;
						if (${$league . "GameStatus"} != "in") {
							WriteSettingToFile("{$league}GameStatus",$status['state'],$pluginName);
						}
						break;
					case "post":
						if ($status['myScore'] > $status['oppoScore']) {
							if (${$league . "WinSequence"} != 'none') {
								insertPlaylistImmediate(${$league . "WinSequence"});
								logEntry("Your {$league} team won! Playing sequence.");								
							} else {
								logEntry("Your {$league} team won but no sequence selected");
							}
						}
						WriteSettingToFile("{$league}GameStatus",$status['state'],$pluginName);
						${$league . "SleepTime"} = 600;
						break;
					default:
						WriteSettingToFile("{$league}GameStatus",$status['state'],$pluginName);
						${$league . "SleepTime"} = 600;					
				}

		}
	
	}

	return min($nflSleepTime, $ncaaSleepTime, $nhlSleepTime);
}
	
function insertPlaylistImmediate($playlist) {
  $playlist .= '.fseq';
  $playlist = rawurlencode($playlist);
  $url = "http://127.0.0.1/api/command/Insert%20Playlist%20Immediate/" . $playlist . "/0/0";
  $options = array(
    'http' => array(
      'method'  => 'GET'
      )
  );
  $context = stream_context_create( $options );
  $result = file_get_contents( $url, false, $context );
}

function logEntry($data) {

	global $logFile,$myPid;

	$data = $_SERVER['PHP_SELF']." : [".$myPid."] ".$data;
	
	$logWrite= fopen($logFile, "a") or die("Unable to open file!");
	fwrite($logWrite, date('Y-m-d h:i:s A',time()).": ".$data."\n");
	fclose($logWrite);
}
	
?>
