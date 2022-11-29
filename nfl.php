<?php
$skipJSsettings = true;
include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';
$pluginName = basename(dirname(__FILE__));
$pluginPath = $settings['pluginDirectory']."/".$pluginName."/"; 
$logFile = $settings['logDirectory']."/".$pluginName.".log";
$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
$pluginSettings = parse_ini_file($pluginConfigFile);

logEntry("Starting NFL Plugin");

//initialize config file
foreach (array('nfl', 'ncaa', 'nhl', 'mlb') as $league) {

	if (strlen(urldecode($pluginSettings["{$league}TeamID"]))<1){
	  WriteSettingToFile("{$league}TeamID",urlencode(""),$pluginName);
	}
	if (strlen(urldecode($pluginSettings["{$league}TeamAbbreviation"]))<1){
		WriteSettingToFile("{$league}TeamAbbreviation",urlencode(""),$pluginName);
	}
	if (strlen(urldecode($pluginSettings["{$league}TeamLogo"]))<1){
	  WriteSettingToFile("{$league}TeamLogo",urlencode(""),$pluginName);
	}
	if (strlen(urldecode($pluginSettings["{$league}Start"]))<1){
	  WriteSettingToFile("{$league}Start",urlencode("0"),$pluginName);
	}
	if (strlen(urldecode($pluginSettings["{$league}GameStatus"]))<1){
	  WriteSettingToFile("{$league}GameStatus",urlencode(""),$pluginName);
	}
	if (strlen(urldecode($pluginSettings["{$league}OppoID"]))<1){
	  WriteSettingToFile("{$league}OppoID",urlencode(""),$pluginName);
	}
	if (strlen(urldecode($pluginSettings["{$league}OppoName"]))<1){
		WriteSettingToFile("{$league}OppoName",urlencode(""),$pluginName);
	}	
	if (strlen(urldecode($pluginSettings["{$league}WinSequence"]))<1){
	  WriteSettingToFile("{$league}WinSequence",urlencode(""),$pluginName);
	}
	if (strlen(urldecode($pluginSettings["{$league}MyScore"]))<1){
		WriteSettingToFile("{$league}MyScore",urlencode("0"),$pluginName);
	}
	if (strlen(urldecode($pluginSettings["{$league}OppoScore"]))<1){
		WriteSettingToFile("{$league}OppoScore",urlencode("0"),$pluginName);
	}
  
	if ($league == "nfl" || $league == "ncaa") {
  
	  if (strlen(urldecode($pluginSettings["${league}TouchdownSequence"]))<1){
		WriteSettingToFile("${league}TouchdownSequence",urlencode(""),$pluginName);
	  }
	  if (strlen(urldecode($pluginSettings["${league}FieldgoalSequence"]))<1){
		WriteSettingToFile("${league}FieldgoalSequence",urlencode(""),$pluginName);
	  }
  
	} elseif ($league == "nhl" || $league == "mlb") {
  
	  if (strlen(urldecode($pluginSettings["{$league}ScoreSequence"]))<1){
		WriteSettingToFile("{$league}ScoreSequence",urlencode(""),$pluginName);
	  }
  
	}
  
}

if (strlen(urldecode($pluginSettings['logLevel']))<1){
	WriteSettingToFile("logLevel",urlencode("2"),$pluginName);
}
if (strlen(urldecode($pluginSettings['ENABLED']))<1){
	WriteSettingToFile("ENABLED",urlencode("OFF"),$pluginName);
}

$loopState=true;

while($loopState) {
	$pluginSettings = parse_ini_file($pluginConfigFile); //check if needed
	$enabledState= urldecode($pluginSettings['ENABLED']);
	if ($enabledState== "OFF"){
		$loopState=false; 
		break;
	}else{
		$loopState=true;
	}		
$sleepTime=updateTeamStatus ();
sleep($sleepTime);
}

?>