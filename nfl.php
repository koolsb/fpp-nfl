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
$teamID = "";

$loopState=true;

while($loopState) {
	$pluginSettings = parse_ini_file($pluginConfigFile); //check if needed
	$enabledState= urldecode($pluginSettings['ENABLED']);
	$teamID = urldecode($pluginSettings['teamID']);
		
	if ($enabledState== "OFF"){
		$loopState=false; 
	}else{
		$loopState=true;
	}		
$sleepTime=updateTeamStatus ();
sleep($sleepTime);
}

?>