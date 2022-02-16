<?php
include_once "/opt/fpp/www/common.php";
$pluginName = basename(dirname(__FILE__));
$pluginPath = $settings['pluginDirectory']."/".$pluginName."/"; 
$logFile = $settings['logDirectory']."/".$pluginName.".log";
$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
$pluginSettings = parse_ini_file($pluginConfigFile);

WriteSettingToFile("nfl_enabled",urlencode("true"),$pluginName);
WriteSettingToFile("nfl_restarting",urlencode("false"),$pluginName);

echo "Starting NFL Plugin\n";
logEntry("Starting NFL Plugin");

$teamID = "";
$touchdownSequence = "";
$winSequence = "";
$teamScore = 0;
$oppoScore = 0;
$gameState = 'pre';
$sleepTime = 600;


while(true) {
  $pluginSettings = parse_ini_file($pluginConfigFile);
  $teamID = urldecode($pluginSettings['teamID']);
  $touchdownSequence = urldecode($pluginSettings['touchdownSequence']);
  $winSequence = urldecode($pluginSettings['winSequence']);

  if ($teamID != '') {
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

    foreach($games as $game) {
      if (strpos($game['shortName'], $teamID) !== false) {
        if ($game['competitions'][0]['competitors'][0]['team']['abbreviation'] == $teamID) {
          $teamIndex = 0;
          $oppoIndex = 1;
        } else {
           $teamIndex = 1;
           $oppoIndex = 0;
        }

        //check score changes
        if ($teamScore + 6 == $game['competitions'][0]['competitors'][$teamIndex]['score']) {
          //play touchdown sequence if set
          if ($touchdownSequence != '') {
            insertPlaylistImmediate($touchdownSequence);
            logEntry("Touchdown! Playing sequence.");
          }
        }
        $teamScore = $game['competitions'][0]['competitors'][$teamIndex]['score'];
        $oppoScore = $game['competitions'][0]['competitors'][$oppoIndex]['score'];

        //update sleep timer
        if ($game['status']['type']['state'] == 'pre') {
          $gameState = 'pre';
          $now = new DateTime();
          $gameDate = new DateTime($game['date']);
          $timeToGame = $gameDate->getTimestamp() - $now->getTimestamp();
          if ($timeToGame < 1200) {
            $sleepTime = 5;
          }
        } elseif ($game['status']['type']['state'] == 'in') {
          $gameState = 'in';
          $sleepTime = 5;
        } elseif  ($game['status']['type']['state'] == 'post'){
          if ($gameState == 'in') {
            if ($teamScore > $oppoScore) {
              if ($winSequence != '') {
                insertPlaylistImmediate($winSequence);
                logEntry("Your team won! Playing sequence.");
              }
            }
          }
          $gameState = 'post';
          $sleepTime = 600;
        } else {
          $sleepTime = 600;
        }
        break;
      } else {
        logEntry("Team not found this week.");
      }
    }      
  }

  sleep($sleepTime);

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