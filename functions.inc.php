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
        case 'getNFLLogo' : updateTeam();
			break;
		case 'getNCAALogo' : updateTeam("ncaa");
			break;
        case 'blah' : blah();
			break;        
    }
}

function getNFLTeams(){
	$url = "http://site.api.espn.com/apis/site/v2/sports/football/nfl/teams";
	$options = array(
	'http' => array(
		'method'  => 'GET',
		)
	);
	$context = stream_context_create( $options );
	$result = file_get_contents( $url, false, $context );
	$result = json_decode($result, true);
	$teams = $result['sports']['0']['leagues']['0']['teams'];
	foreach ($teams as $team) {
		$team = $team['team'];
        $teamNames[$team['displayName']] = $team['abbreviation'];		
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
	$sequenceList["No Sequence"]="none";
	foreach ($sequences as $sequence) {		
        $sequenceList[$sequence]=$sequence;		
	}		
	return $sequenceList;
}

function getTeamInfo($team, $league="nfl"){
	if ($league == "ncaa") {
		$league = "college-football";
	}
	$url = "http://site.api.espn.com/apis/site/v2/sports/football/{$league}/teams/{$team}";
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
	$teamInfo["groupID"] = $result['team']['groups']['id'];
	return $teamInfo;

}

function updateTeam($league="nfl"){
	logEntry("Updating {$league} Team and logo");
	global $pluginName;
	global $pluginSettings;

	//clear old scores
	WriteSettingToFile("{$league}MyScore",0,$pluginName);
	WriteSettingToFile("{$league}OppoScore",0,$pluginName);

	//configure variables
	if (strlen(urldecode($pluginSettings["{$league}TeamID"]))>1){
		$teamID=urldecode($pluginSettings["{$league}TeamID"]);
		$teamInfo = getTeamInfo($teamID, $league);
		$teamLogo = $teamInfo['logo'];
		$teamGroupID = $teamInfo['groupID'];
		$teamAbbreviation = $teamInfo['abbreviation'];
	}else{
		$teamLogo = "";
	}
	WriteSettingToFile("{$league}TeamLogo",$teamLogo,$pluginName);
	WriteSettingToFile("{$league}TeamGroupID",$teamGroupID,$pluginName);
	WriteSettingToFile("{$league}TeamAbbreviation",$teamAbbreviation,$pluginName);
	logEntry("{$league} Team Group ID Updated " . $teamGroupID);
	logEntry("{$league} Logo updated " . $teamLogo);
	logEntry("{$league} Abbreviation updated " . $teamAbbreviation);
	updateTeamStatus(true);
	return $teamLogo;

}

function updateTeamStatus($reparseSettings=false){
	//initialize globals
	global $logFile;	
	global $pluginConfigFile;
	global $pluginName;
	global $pluginSettings; 

	//reparse settings file - needs to reread team group id on change
	if ($reparseSettings) {
		if (file_exists($pluginConfigFile)) {
			$pluginSettings = parse_ini_file ($pluginConfigFile);
		  } else {	
			  $pluginSettings="";
			  logEntry("No pluginConfigFile");
		  }
	}
	
	//get settings
	if (strlen(urldecode($pluginSettings['nflTeamID']))>1){
		$nflTeamID=urldecode($pluginSettings['nflTeamID']);
	} else {
		$nflTeamID="";
	}
	if (strlen(urldecode($pluginSettings['nflTouchdownSequence']))>1){
		$nflTouchdownSequence=urldecode($pluginSettings['nflTouchdownSequence']);
	} else {
		$nflTouchdownSequence="none";
	}
	if (strlen(urldecode($pluginSettings['nflFieldgoalSequence']))>1){
		$nflFieldgoalSequence=urldecode($pluginSettings['nflFieldgoalSequence']);
	} else {
		$nflFieldgoalSequence="none";
	}
	if (strlen(urldecode($pluginSettings['nflWinSequence']))>1){
		$nflWinSequence=urldecode($pluginSettings['nflWinSequence']);
	} else {
		$nflWinSequence="none";
	}
	if (strlen(urldecode($pluginSettings['nflOppoID']))>1){		
		$nflOppoID=urldecode($pluginSettings['nflOppoID']);
	}else{		
		$nflOppoID="";
	}
	if (strlen(urldecode($pluginSettings['nflKickoff']))>1){
		$nflKickoff=urldecode($pluginSettings['nflKickoff']);
	}else{
		$nflKickoff="";
	}
	if (strlen(urldecode($pluginSettings['nflGameStatus']))>1){
		$nflGameStatus=urldecode($pluginSettings['nflGameStatus']);
	}else{
		$nflGameStatus="";
	}
	if (strlen(urldecode($pluginSettings['nflMyScore']))>0){
		$nflMyScore=urldecode($pluginSettings['nflMyScore']);
	}else{
		$nflMyScore="0";
	}
	if (strlen(urldecode($pluginSettings['nflOppoScore']))>0){
		$nflOppoScore=urldecode($pluginSettings['nflOppoScore']);
	}else{
		$nflOppoScore="0";
	}

	if (strlen(urldecode($pluginSettings['ncaaTeamID']))>1){
		$ncaaTeamID=urldecode($pluginSettings['ncaaTeamID']);
	} else {
		$ncaaTeamID="";
	}
	if (strlen(urldecode($pluginSettings['ncaaTeamAbbreviation']))>1){
		$ncaaTeamAbbreviation=urldecode($pluginSettings['ncaaTeamAbbreviation']);
	} else {
		$ncaaTeamAbbreviation="";
	}
	if (strlen(urldecode($pluginSettings['ncaaTeamGroupID']))>1){
		$ncaaTeamGroupID=urldecode($pluginSettings['ncaaTeamGroupID']);
	} else {
		$ncaaTeamGroupID="";
	}
	if (strlen(urldecode($pluginSettings['ncaaTouchdownSequence']))>1){
		$ncaaTouchdownSequence=urldecode($pluginSettings['ncaaTouchdownSequence']);
	} else {
		$ncaaTouchdownSequence="none";
	}
	if (strlen(urldecode($pluginSettings['ncaaFieldgoalSequence']))>1){
		$ncaaFieldgoalSequence=urldecode($pluginSettings['ncaaFieldgoalSequence']);
	} else {
		$ncaaFieldgoalSequence="none";
	}
	if (strlen(urldecode($pluginSettings['ncaaWinSequence']))>1){
		$ncaaWinSequence=urldecode($pluginSettings['ncaaWinSequence']);
	} else {
		$ncaaWinSequence="none";
	}
	if (strlen(urldecode($pluginSettings['ncaaOppoID']))>1){		
		$ncaaOppoID=urldecode($pluginSettings['ncaaOppoID']);
	}else{		
		$ncaaOppoID="";
	}
	if (strlen(urldecode($pluginSettings['ncaaKickoff']))>1){
		$ncaaKickoff=urldecode($pluginSettings['ncaaKickoff']);
	}else{
		$ncaaKickoff="";
	}
	if (strlen(urldecode($pluginSettings['ncaaGameStatus']))>1){
		$ncaaGameStatus=urldecode($pluginSettings['ncaaGameStatus']);
	}else{
		$ncaaGameStatus="";
	}
	if (strlen(urldecode($pluginSettings['ncaaMyScore']))>0){
		$ncaaMyScore=urldecode($pluginSettings['ncaaMyScore']);
	}else{
		$ncaaMyScore="0";
	}
	if (strlen(urldecode($pluginSettings['ncaaOppoScore']))>0){
		$ncaaOppoScore=urldecode($pluginSettings['ncaaOppoScore']);
	}else{
		$ncaaOppoScore="0";
	}

	if (strlen(urldecode($pluginSettings['logLevel']))>0){
		$logLevel=urldecode($pluginSettings['logLevel']);
	}else{
		$logLevel=0;
	}
		
	$nflSleepTime = 600; 
	$ncaaSleepTime = 600;
	
	if ($nflTeamID != '') {
		
		//log polling
		if ($logLevel >= 5) {
			logEntry("Polling ESPN NFL API");
			echo "Polling ESPN NFL API";
		}
		
		//get NFL score
		$url = "http://site.api.espn.com/apis/site/v2/sports/football/nfl/scoreboard";
		$options = array(
		'http' => array(
			'method'  => 'GET',
			)
		);
		
		$context = stream_context_create( $options );
		$games = file_get_contents( $url, false, $context );
		$games = json_decode($games, true);
		$games = $games['events'];

		$gameFound = false;

		foreach($games as $game) {
			if (strpos($game['shortName'], $nflTeamID) !== false) {
				$gameFound = true;

				// set kickoff time
				WriteSettingToFile("nflKickoff",$game['date'],$pluginName);				

				if ($game['competitions'][0]['competitors'][0]['team']['abbreviation'] == $nflTeamID) {
					$nflTeamIndex = 0;
					$nflOppoIndex = 1;
				} else {
					$nflTeamIndex = 1;
					$nflOppoIndex = 0;
				}

				// set opponent ID
				if ($nflOppoID != $game['competitions'][0]['competitors'][$nflOppoIndex]['team']['abbreviation']) {
					WriteSettingToFile("nflOppoID",$game['competitions'][0]['competitors'][$nflOppoIndex]['team']['abbreviation'],$pluginName);
					WriteSettingToFile("nflOppoName",$game['competitions'][0]['competitors'][$nflOppoIndex]['team']['displayName'],$pluginName);
				}
			
				//get current scores
				$nflNewTeamScore = $game['competitions'][0]['competitors'][$nflTeamIndex]['score'];
				$nflNewOppoScore = $game['competitions'][0]['competitors'][$nflOppoIndex]['score'];
			
				//check score changes
				if ($nflMyScore + 6 == $nflNewTeamScore) {
					//play touchdown sequence if set
					if ($nflTouchdownSequence != 'none') {
						insertPlaylistImmediate($nflTouchdownSequence);
						logEntry("NFL Touchdown! Playing sequence.");					
					} else {
						logEntry("NFL Touchdown Triggered but no sequence selected");
					}
				} elseif ($nflMyScore + 3 == $nflNewTeamScore) {
					//play fieldgoal sequence if set
					if ($nflFieldgoalSequence != 'none') {
						insertPlaylistImmediate($nflFieldgoalSequence);
						logEntry("NFL Fieldgoal! Playing sequence.");					
					} else {
						logEntry("NFL Fieldgoal Triggered but no sequence selected");
					}
				}
			
				//update stored scores
				if ($nflMyScore != $nflNewTeamScore) {
					WriteSettingToFile("nflMyScore",$nflNewTeamScore,$pluginName);
				}
				if ($nflOppoScore != $nflNewOppoScore) {
					WriteSettingToFile("nflOppoScore",$nflNewOppoScore,$pluginName);
				}

				//update sleep timer
				switch ($game['status']['type']['state']){
					case "pre":
						$now = new DateTime();
						$gameDate = new DateTime($game['date']);
						$timeToGame = $gameDate->getTimestamp() - $now->getTimestamp();
						if ($timeToGame < 1200) {
							$nflSleepTime = 30;
						}
						break;
					case "in":						
						$nflSleepTime = 5;
						break;
					case "post":
						if ($nflGameStatus == 'in') {
							if ($nflNewTeamScore > $nflNewOppoScore) {
								if ($nflWinSequence != 'none') {
									insertPlaylistImmediate($nflWinSequence);
									logEntry("Your NFL team won! Playing sequence.");								
								} else {
									logEntry("Your NFL team won but no sequence selected");
								}
							}
						}
						$nflSleepTime = 600;
						break;
					default:
						$nflSleepTime = 600;					
				}

				//update stored game status
				if ($nflGameStatus != $game['status']['type']['state']) {
					WriteSettingToFile("nflGameStatus",$game['status']['type']['state'],$pluginName);
				}

				break;
			}
			
		}
        
		//log if no game found
		switch ($gameFound){
			case true:
				if ($game['date'] != $nflKickoff){
					$nflKickoff = $game['date'];
				}
				break;
			default:
				logEntry("Your NFL team is not playing this week.");
				WriteSettingToFile("nflKickoff","0",$pluginName);
		}	
	}

	if ($ncaaTeamID != '') {
		
		//log polling
		if ($logLevel >= 5) {
			logEntry("Polling ESPN NCAA API");
			echo "Polling ESPN NCAA API";
		}

		//get NCAA score
		$url = "http://site.api.espn.com/apis/site/v2/sports/football/college-football/scoreboard?groups={$ncaaTeamGroupID}";
		$options = array(
		'http' => array(
			'method'  => 'GET',
			)
		);
		
		$context = stream_context_create( $options );
		$games = file_get_contents( $url, false, $context );
		$games = json_decode($games, true);
		$games = $games['events'];

		$gameFound = false;

		foreach($games as $game) {
			if (strpos($game['shortName'], $ncaaTeamAbbreviation) !== false) {
				$gameFound = true;

				// set kickoff time
				WriteSettingToFile("ncaaKickoff",$game['date'],$pluginName);				

				if ($game['competitions'][0]['competitors'][0]['team']['abbreviation'] == $ncaaTeamAbbreviation) {
					$ncaaTeamIndex = 0;
					$ncaaOppoIndex = 1;
				} else {
					$ncaaTeamIndex = 1;
					$ncaaOppoIndex = 0;
				}

				// set opponent ID
				if ($ncaaOppoID != $game['competitions'][0]['competitors'][$ncaaOppoIndex]['team']['abbreviation']) {
					WriteSettingToFile("ncaaOppoID",$game['competitions'][0]['competitors'][$ncaaOppoIndex]['team']['abbreviation'],$pluginName);
					WriteSettingToFile("ncaaOppoName",$game['competitions'][0]['competitors'][$ncaaOppoIndex]['team']['displayName'],$pluginName);
				}
			
				//get current scores
				$ncaaNewTeamScore = $game['competitions'][0]['competitors'][$ncaaTeamIndex]['score'];
				$ncaaNewOppoScore = $game['competitions'][0]['competitors'][$ncaaOppoIndex]['score'];
			
				//check score changes
				if ($ncaaMyScore + 6 == $ncaaNewTeamScore) {
					//play touchdown sequence if set
					if ($ncaaTouchdownSequence != 'none') {
						insertPlaylistImmediate($caaTouchdownSequence);
						logEntry("NCAA Touchdown! Playing sequence.");					
					} else {
						logEntry("NCAA Touchdown Triggered but no sequence selected");
					}
				} elseif ($ncaaMyScore + 3 == $ncaaNewTeamScore) {
					//play fieldgoal sequence if set
					if ($ncaaFieldgoalSequence != 'none') {
						insertPlaylistImmediate($ncaaFieldgoalSequence);
						logEntry("NCAA Fieldgoal! Playing sequence.");					
					} else {
						logEntry("NCAA Fieldgoal Triggered but no sequence selected");
					}
				}
			
				//update stored scores
				if ($ncaaMyScore != $ncaaNewTeamScore) {
					WriteSettingToFile("ncaaMyScore",$ncaaNewTeamScore,$pluginName);
				}
				if ($ncaaOppoScore != $ncaaNewOppoScore) {
					WriteSettingToFile("ncaaOppoScore",$ncaaNewOppoScore,$pluginName);
				}

				//update sleep timer
				switch ($game['status']['type']['state']){
					case "pre":
						$now = new DateTime();
						$gameDate = new DateTime($game['date']);
						$timeToGame = $gameDate->getTimestamp() - $now->getTimestamp();
						if ($timeToGame < 1200) {
							$ncaaSleepTime = 30;
						}
						break;
					case "in":						
						$ncaaSleepTime = 5;
						break;
					case "post":
						if ($ncaaGameStatus == 'in') {
							if ($ncaaNewTeamScore > $ncaaNewOppoScore) {
								if ($ncaaWinSequence != 'none') {
									insertPlaylistImmediate($ncaaWinSequence);
									logEntry("Your NCAA team won! Playing sequence.");								
								} else {
									logEntry("Your NCAA team won but no sequence selected");
								}
							}
						}
						$ncaaSleepTime = 600;
						break;
					default:
						$ncaaSleepTime = 600;					
				}

				//update stored game status
				if ($ncaaGameStatus != $game['status']['type']['state']) {
					WriteSettingToFile("ncaaGameStatus",$game['status']['type']['state'],$pluginName);
				}

				break;
			}
			
		}
        
		//log if no game found
		switch ($gameFound){
			case true:
				if ($game['date'] != $ncaaKickoff){
					$ncaaKickoff = $game['date'];
				}
				break;
			default:
				logEntry("Your NCAA team is not playing this week.");
				WriteSettingToFile("ncaaKickoff","0",$pluginName);
		}
	}


	return min($nflSleepTime, $ncaaSleepTime);
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
