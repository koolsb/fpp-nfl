<?php
$skipJSsettings = true;
include_once "/opt/fpp/www/common.php";
$pluginName = basename(dirname(__FILE__));
$pluginPath = $settings['pluginDirectory']."/".$pluginName."/"; 
$logFile = $settings['logDirectory']."/".$pluginName.".log";
$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
$pluginSettings = parse_ini_file($pluginConfigFile);

echo "Starting NFL Plugin\n";
logEntry("Starting NFL Plugin");

$teamID = "";
$touchdownSequence = "";
$fieldgoalSequence = "";
$winSequence = "";
$teamScore = 0;
$oppoScore = 0;
$gameState = 'pre';
$sleepTime = 600;

//game status fields
$opponentID = "";
$kickoff = "";
$gameStatus = "";
$myScore = "";
$theirScore = "";


while(true) {
  $pluginSettings = parse_ini_file($pluginConfigFile);
  $teamID = urldecode($pluginSettings['teamID']);
  $touchdownSequence = urldecode($pluginSettings['touchdownSequence']);
  $fieldgoalSequence = urldecode($pluginSettings['fieldgoalSequence']);
  $winSequence = urldecode($pluginSettings['winSequence']);
  $myScore = urldecode($pluginSettings['myScore']);
  $theirScore = urldecode($pluginSettings['theirScore']);


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
      echo $game['shortName'];
      if (strpos($game['shortName'], $teamID) !== false) {

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

        //check score changes
        if ($teamScore + 6 == $game['competitions'][0]['competitors'][$teamIndex]['score']) {
          //play touchdown sequence if set
          if ($touchdownSequence != '') {
            insertPlaylistImmediate($touchdownSequence);
            logEntry("Touchdown! Playing sequence.");
            echo "Touchdown! Playing sequence.";
          }
        } elseif ($teamScore + 3 == $game['competitions'][0]['competitors'][$teamIndex]['score']) {
          //play fieldgoal sequence if set
          if ($fieldgoalSequence != '') {
            insertPlaylistImmediate($fieldgoalSequence);
            logEntry("Fieldgoal! Playing sequence.");
            echo "Fieldgoal! Playing sequence.";
          }
        }
        
        //update scores
        $teamScore = $game['competitions'][0]['competitors'][$teamIndex]['score'];
        $oppoScore = $game['competitions'][0]['competitors'][$oppoIndex]['score'];

        //update stored scores
        if ($teamScore != $myScore) {
          WriteSettingToFile("myScore",$game['competitions'][0]['competitors'][$teamIndex]['score'],$pluginName);
        }
        if ($oppoScore != $theirScore) {
          WriteSettingToFile("theirScore",$game['competitions'][0]['competitors'][$oppoIndex]['score'],$pluginName);
        }

        //update stored game status
        if ($gameStatus != $game['status']['type']['state']) {
          WriteSettingToFile("gameStatus",$game['status']['type']['state'],$pluginName);
        }

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
                echo "Your team won! Playing sequence.";
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
        echo "Team not found this week.";
        WriteSettingToFile("kickoff","0",$pluginName);
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