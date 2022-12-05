<?php
include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';

$pluginName = basename(dirname(__FILE__));
$pluginConfigFile = $settings['configDirectory'] ."/plugin." .$pluginName;
    
if (file_exists($pluginConfigFile)) {
  $pluginSettings = parse_ini_file($pluginConfigFile);
}

foreach ($pluginSettings as $key => $value) { 
  ${$key} = urldecode($value);
}

?>
<!DOCTYPE html>
<html>
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
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
            <h1>Pro Sports Scoring Plugin</h1>
          </div>
        </div>
      </div>
    <div class="container-fluid">
      <div class="card">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="true">Settings</button>
          </li>
          <?php foreach ($leagues as $league) { ?>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="<?=$league?>-tab" data-bs-toggle="tab" data-bs-target="#<?=$league?>" type="button" role="tab" aria-controls="<?=$league?>" aria-selected="false"><?=strtoupper($league)?></button>
          </li>
          <?php } ?>
        </ul>
        <div class="tab-content" id="myTabContent">
          <div class="tab-pane fade show active" id="settings" role="tabpanel" aria-labelledby="settings-tab">
            <!-- Enable Plugin -->
            <div class="justify-content-md-center row pt-4">
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
            <!-- Log Level -->
            <div class="justify-content-md-center row">
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
          </div>
          <!-- tab pages -->
          <?php
          foreach ($leagues as $league) { 
            switch ($league) {
              case 'nfl' : $sport = "football";
                break;
              case 'ncaa' : $sport = "football";
                break;
              case 'nhl' : $sport = "hockey";
                break; 
              case 'mlb' : $sport = "baseball";
                break;
            }
          ?>
          <div class="tab-pane fade" id="<?=$league?>" role="tabpanel" aria-labelledby="<?=$league?>-tab">
            <div style= "height:100; width:100; margin:auto" class="pt-3">
              <img id="<?=$league?>LogoImage" src="<?echo ${$league . "TeamLogo"};?>" width="100" height ="100">
            </div>
            <!-- <?=$league?> Team -->
            <div class="justify-content-md-center row pt-5 py-4">		
              <div class="col-md-6">		  
                <div class="card-title h5">
                  <?=strtoupper($league)?> Team
                </div>
                <div class="mb-2 text-muted small h6">
                  Select your <?=strtoupper($league)?> team
                </div>
              </div>
              <div class="col-md-6">            
                  <div class="input-group">				
                    <? PrintSettingSelect($league . "TeamID", $league . "TeamID", 0, 0, $defaultValue="", getTeams($sport, $league), $pluginName, $callbackName = "update" . strtoupper($league) . "Logo", $changedFunction = "");?>				
                  </div>            
              </div>
            </div>
            <?php if ($sport == "football") { ?>
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
                    <? PrintSettingSelect($league . "TouchdownSequence", $league . "TouchdownSequence", 0, 0, $defaultValue="", getSequences(), $pluginName, $callbackName = "", $changedFunction = ""); ?>
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
                    <? PrintSettingSelect($league . "FieldgoalSequence", $league . "FieldgoalSequence", 0, 0, $defaultValue="", getSequences(), $pluginName, $callbackName = "", $changedFunction = ""); ?>                
                  </div>           
              </div>
            </div>
            <?php } else { ?>
            <!-- Run Sequence -->
            <div class="justify-content-md-center row pb-5">
              <div class="col-md-6">
                <div class="card-title h5">
                  Score Sequence
                </div>
                <div class="mb-2 text-muted small h6">
                  Select the sequence to play on a score<br>Select no sequence to disable
                </div>
              </div>
              <div class="col-md-6">            
                  <div class="input-group">
                    <? PrintSettingSelect($league . "ScoreSequence", $league . "ScoreSequence", 0, 0, $defaultValue="", getSequences(), $pluginName, $callbackName = "", $changedFunction = ""); ?>
                  </div>            
              </div>
            </div>
            <?php } ?>
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
                    <? PrintSettingSelect($league . "WinSequence", $league . "WinSequence", 0, 0, $defaultValue="", getSequences(), $pluginName, $callbackName = "", $changedFunction = ""); ?>                
                  </div>            
              </div>
            </div>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <script>
  <?php foreach ($leagues as $league) { ?>
  function update<?=strtoupper($league)?>Logo(){
    $.ajax({ 
      url: 'plugin.php?_menu=status&plugin=fpp-nfl&nopage=1&page=functions.inc.php',
          data: {action: 'update<?=strtoupper($league)?>Team'},
          type: 'post',
          success: function(output) {
              $.ajax({ 
        url: 'api/plugin/fpp-nfl/settings/<?=$league?>TeamLogo',       
        type: 'get',
        success: function(data) {		
          var logo= data.<?=$league?>TeamLogo;
          document.getElementById('<?=$league?>LogoImage').src = logo;					
        }
      });
          }
    });
      
  }
  <?php } ?>
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