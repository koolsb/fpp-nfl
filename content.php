<?php
include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';

$pluginName = basename(dirname(__FILE__));
$pluginConfigFile = $settings['configDirectory'] ."/plugin." .$pluginName;
    
if (file_exists($pluginConfigFile)) {
  $pluginSettings = parse_ini_file($pluginConfigFile);
}

//set defaults if nothing saved
if (strlen(urldecode($pluginSettings['teamID']))<1){
  WriteSettingToFile("teamID",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['touchdownSequence']))<1){
  WriteSettingToFile("touchdownSequence",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['fieldgoalSequence']))<1){
  WriteSettingToFile("fieldgoalSequence",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['winSequence']))<1){
  WriteSettingToFile("winSequence",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['logLevel']))<1){
  WriteSettingToFile("logLevel",urlencode("4"),$pluginName);
}

foreach ($pluginSettings as $key => $value) { 
  ${$key} = urldecode($value);
}

?>

<!DOCTYPE html>
<html>
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1"
    crossorigin="anonymous">
  <style>
    #bodyWrapper {
      background-color: #20222e;
    }
    .pageContent {
      background-color: #171720;
    }
    .plugin-body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: rgb(238, 238, 238);
      background-color: rgb(0, 0, 0);
      font-size: 1rem;
      font-weight: 400;
      line-height: 1.5;
      padding-bottom: 2em;
      background-repeat: no-repeat;
      background-attachment: fixed;
      background-position: top center;
      background-size: auto 100%;
    }
    .card {
      background-color: rgba(59, 69, 84, 0.7);
      border-radius: 0.5em;
      margin: 1em 1em 1em 1em;
      padding: 1em 1em 1em 1em;
    }
  </style>
</head>
<body>
  <div class="container-fluid plugin-body">
    <div class="container-fluid pt-4">
      <div class="card">
        <div class="justify-content-md-center row py-3">
          <div class="col-md-auto">
            <h1>NFL Touchdown Plugin</h1>
          </div>
        </div>
      </div>
    <div class="container-fluid">
	
      <div class="card">
		<div  style= "height:100; width:100; margin:auto">
			<img id="logoImage" src="<?echo $teamLogo;?>" width="100" height ="100">
		</div>
        <!-- NFL Team -->
        <div class="justify-content-md-center row pt-4 pb-4">		
          <div class="col-md-6">		  
            <div class="card-title h5">
              NFL Team
            </div>
            <div class="mb-2 text-muted small h6">
              Select your NFL team<br><br>
              The ESPN API is only polled on 10 minute intervals when your team is not<br>playing. 
              Changing your team may not be reflected until the next polling interval.
            </div>
          </div>
          <div class="col-md-6">            
              <div class="input-group">				
                <? PrintSettingSelect("teamID", "teamID", 0, 0, $defaultValue="", getTeamList(), $pluginName, $callbackName = "updateLogo", $changedFunction = "");?>				
              </div>            
          </div>
        </div>
        <!-- Touchdown Sequence -->
        <div class="justify-content-md-center row pb-5">
          <div class="col-md-6">
            <div class="card-title h5">
              Touchdown Sequence
            </div>
            <div class="mb-2 text-muted small h6">
              Select the sequence to play on a touchdown<br>Select no sequence to disable
            </div>
          </div>
          <div class="col-md-6">            
              <div class="input-group">
                <? PrintSettingSelect("touchdownSequence", "touchdownSequence", 0, 0, $defaultValue="", getSequences(), $pluginName, $callbackName = "", $changedFunction = ""); ?>
              </div>            
          </div>
        </div>
        <!-- Fieldgoal Sequence -->
        <div class="justify-content-md-center row pb-5">
          <div class="col-md-6">
            <div class="card-title h5">
              Fieldgoal Sequence
            </div>
            <div class="mb-2 text-muted small h6">
              Select the sequence to play on a fieldgoal<br>Select no sequence to disable
            </div>
          </div>
          <div class="col-md-6">            
              <div class="input-group">
                 <? PrintSettingSelect("fieldgoalSequence", "fieldgoalSequence", 0, 0, $defaultValue="", getSequences(), $pluginName, $callbackName = "", $changedFunction = ""); ?>                
              </div>           
          </div>
        </div>
        <!-- Win Sequence -->
        <div class="justify-content-md-center row pb-5">
          <div class="col-md-6">
            <div class="card-title h5">
              Win Sequence
            </div>
            <div class="mb-2 text-muted small h6">
              Select the sequence to play if your team wins<br>Select no sequence to disable
            </div>
          </div>
          <div class="col-md-6">            
              <div class="input-group">
                <? PrintSettingSelect("winSequence", "winSequence", 0, 0, $defaultValue="", getSequences(), $pluginName, $callbackName = "", $changedFunction = ""); ?>                
              </div>            
          </div>
        </div>
        <!-- Log Level -->
        <div class="justify-content-md-center row ">
          <div class="col-md-6">
            <div class="card-title h5">
              Log Level
            </div>
            <div class="mb-2 text-muted small h6">
              Info: Logs each sequence played<br>Debug: Logs each poll to ESPN API
            </div>
          </div>
          <div class="col-md-6">            
              <div class="input-group">
			  <? PrintSettingSelect("logLevel", "logLevel", 0, 0, $defaultValue="", Array("Info" => "4", "Debug" => "5"), $pluginName, $callbackName = "", $changedFunction = ""); ?>               
              </div>            
          </div>
        </div>
		<div class="justify-content-md-center row ">
			<div class="col-md-6">
				<div class="card-title h5">
					Enable Plugin
				</div>
				<div class="mb-2 text-muted small h6">
				The plugin is enabled when checked
				</div>
          </div>
          <div class="col-md-6">            
              <div>
			  <?PrintSettingCheckbox("NFLPlugin", "ENABLED", 0, 0, "ON", "OFF", $pluginName ,$callbackName = "setEnabledStatus", $changedFunction = ""); ?>               
              </div>            
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
  
  function updateLogo(){
		
	$.ajax({ 
		url: 'plugin.php?_menu=status&plugin=fpp-nfl&nopage=1&page=functions.inc.php',
        data: {action: 'getLogo'},
        type: 'post',
        success: function(output) {
            $.ajax({ 
			url: 'api/plugin/fpp-nfl/settings/teamLogo',       
			type: 'get',
			success: function(data) {		
				var logo= data.teamLogo;
				document.getElementById('logoImage').src = logo;					
			}
		});
        }
	});
		
  }
  function setEnabledStatus(){
	$.ajax({ 
		url: 'plugin.php?_menu=status&plugin=fpp-nfl&nopage=1&page=nfl.php',
		type: 'post',
        success: function(result) {
			console.log (result);
		}
           
		
       
	});
		
   
  }
  </script>
 
</body>
</html>