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
        case 'getLogo' : getLogo();
			break;
        case 'blah' : blah();
			break;        
    }
}

function getTeams(){
$opponentID = "";
$kickoff = "";
$gameStatus = "";
$myScore = "";
$theirScore = "";

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
return $teams;
}

function getTeamList(){ //get array of teams with display name and abbreviation
	$teamsList= getTeams();  
	foreach ($teamsList as $team) {
		$team = $team['team'];
        $teamNames[$team['displayName']] = $team['abbreviation'];		
	}	
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

function getLogo(){
	logEntry("Updating Team and logo");
	global $pluginName;
	global $pluginSettings;
	
	//clear old scores
	WriteSettingToFile("myScore",0,$pluginName);
	WriteSettingToFile("theirScore",0,$pluginName);
	
	//configure variables
	if (strlen(urldecode($pluginSettings['teamID']))>1){
		$teamID=urldecode($pluginSettings['teamID']);
	}else{
		$teamID=""; 
	}
	
	$teamsList= getTeams();
	$teamLogo = "";
	
	foreach ($teamsList as $team) {		
		$team = $team['team'];
		
		if ($team['abbreviation']==$teamID){
			$teamLogo= $team['logos'][0]['href'];
			WriteSettingToFile("teamLogo",$teamLogo,$pluginName);
			break;
		}         	
	}
	logEntry("Logo updated " . $teamLogo);
	$temp= updateTeamStatus();
	return $teamLogo;	
}

function updateTeamStatus(){
	
	//initialize globals
	global $logFile;	
	global $pluginConfigFile;
	global $pluginName;
	global $pluginSettings; 
	
	//get settings
	if (strlen(urldecode($pluginSettings['teamID']))>1){
		$teamID=urldecode($pluginSettings['teamID']);
	} else {
		$teamID="";
	}
	if (strlen(urldecode($pluginSettings['touchdownSequence']))>1){
		$touchdownSequence=urldecode($pluginSettings['touchdownSequence']);
	} else {
		$touchdownSequence="none";
	}
	if (strlen(urldecode($pluginSettings['fieldgoalSequence']))>1){
		$fieldgoalSequence=urldecode($pluginSettings['fieldgoalSequence']);
	} else {
		$fieldgoalSequence="none";
	}
	if (strlen(urldecode($pluginSettings['winSequence']))>1){
		$winSequence=urldecode($pluginSettings['winSequence']);
	} else {
		$winSequence="none";
	}
	
	if (strlen(urldecode($pluginSettings['logLevel']))>0){
		$logLevel=urldecode($pluginSettings['logLevel']);
	}else{
		$logLevel=0;
	}
		
	if (strlen(urldecode($pluginSettings['opponentID']))>1){		
		$opponentID=urldecode($pluginSettings['opponentID']);
	}else{		
		$opponentID="";
	}
	
	if (strlen(urldecode($pluginSettings['kickoff']))>1){
		$kickoff=urldecode($pluginSettings['kickoff']);
	}else{
		$kickoff="";
	}
	
	if (strlen(urldecode($pluginSettings['myScore']))>0){
		$myScore=urldecode($pluginSettings['myScore']);
	}else{
		$myScore="0";
	}
	
	if (strlen(urldecode($pluginSettings['gameStatus']))>1){
		$gameStatus=urldecode($pluginSettings['gameStatus']);
	}else{
		$gameStatus="";
	}

	//Reset Scores	
	$theirScore= 0;
	$teamScore = 0;
	$oppoScore = 0;
	$sleepTime = 600; 
	
	if ($teamID != '') {
		
		//log polling
		if ($logLevel >= 5) {
			logEntry("Polling ESPN API");
			echo "Polling ESPN API";
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
			if (strpos($game['shortName'], $teamID) !== false) {
				$gameFound = true;

				// set kickoff time
				WriteSettingToFile("kickoff",$game['date'],$pluginName);				

				if ($game['competitions'][0]['competitors'][0]['team']['abbreviation'] == $teamID) {
					$teamIndex = 0;
					$oppoIndex = 1;
				} else {
					$teamIndex = 1;
					$oppoIndex = 0;
				}

				// set opponent ID
				if ($opponentID != $game['competitions'][0]['competitors'][$oppoIndex]['team']['abbreviation']) {
					WriteSettingToFile("opponentID",$game['competitions'][0]['competitors'][$oppoIndex]['team']['abbreviation'],$pluginName);
					WriteSettingToFile("opponentName",$game['competitions'][0]['competitors'][$oppoIndex]['team']['displayName'],$pluginName);
				}
			
				//get current scores
				$teamScore = $game['competitions'][0]['competitors'][$teamIndex]['score'];
				$oppoScore = $game['competitions'][0]['competitors'][$oppoIndex]['score'];
			
				//check score changes
				if ($myScore + 6 == $teamScore) {
					//play touchdown sequence if set
					if ($touchdownSequence != 'none') {
						insertPlaylistImmediate($touchdownSequence);
						logEntry("Touchdown! Playing sequence.");					
					} else {
						logEntry("Touchdown Triggered but no sequence selected");
					}
				} elseif ($myScore + 3 == $teamScore) {
					//play fieldgoal sequence if set
					if ($fieldgoalSequence != 'none') {
						insertPlaylistImmediate($fieldgoalSequence);
						logEntry("Fieldgoal! Playing sequence.");					
					} else {
						logEntry("Fieldgoal Triggered but no sequence selected");
					}
				}
			
				//update stored scores
				if ($teamScore != $myScore) {
					WriteSettingToFile("myScore",$teamScore,$pluginName);
				}
				if ($oppoScore != $theirScore) {
					WriteSettingToFile("theirScore",$game['competitions'][0]['competitors'][$oppoIndex]['score'],$pluginName);
				}

				//update sleep timer
				switch ($game['status']['type']['state']){
					case "pre":
						$now = new DateTime();
						$gameDate = new DateTime($game['date']);
						$timeToGame = $gameDate->getTimestamp() - $now->getTimestamp();
						if ($timeToGame < 1200) {
							$sleepTime = 30;
						}
						break;
					case "in":						
						$sleepTime = 5;
						break;
					case "post":
						if ($gameStatus == 'in') {
							if ($teamScore > $oppoScore) {
								if ($winSequence != 'none') {
									insertPlaylistImmediate($winSequence);
									logEntry("Your team won! Playing sequence.");								
								} else {
									logEntry("Your team won but no sequence selected");
								}
							}
						}
						$sleepTime = 600;
						break;
					default:
						$sleepTime = 600;					
				}

				//update stored game status
				if ($gameStatus != $game['status']['type']['state']) {
					WriteSettingToFile("gameStatus",$game['status']['type']['state'],$pluginName);
				}

				break;
			}
			
		}
        
		//log if no game found
		switch ($gameFound){
			case true:
				if ($game['date'] != $kickoff){
					$kickoff = $game['date'];
				}
				break;
			default:
				logEntry("Your team is not playing this week.");
				WriteSettingToFile("kickoff","0",$pluginName);
		}	
	}
	return $sleepTime;
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
