<?php
include_once "/opt/fpp/www/common.php";
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
if (strlen(urldecode($pluginSettings['winSequence']))<1){
  WriteSettingToFile("winSequence",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['touchdownEnabled']))<1){
  WriteSettingToFile("touchdownEnabled",urlencode("false"),$pluginName);
}
if (strlen(urldecode($pluginSettings['winEnabled']))<1){
  WriteSettingToFile("winEnabled",urlencode("false"),$pluginName);
}

foreach ($pluginSettings as $key => $value) { 
  ${$key} = urldecode($value);
}

$touchdownEnabled = urldecode($pluginSettings['touchdownEnabled']);
$touchdownEnabled = $touchdownEnabled == "true" ? true : false;
$winEnabled = urldecode($pluginSettings['winEnabled']);
$winEnabled = $winEnabled == "true" ? true : false;

if (isset($_POST['updateTeamID'])) { 
  $teamID = trim($_POST['teamID']);
  WriteSettingToFile("teamID",$teamID,$pluginName);
  WriteSettingToFile("nfl_enabled",urlencode("false"),$pluginName);
  WriteSettingToFile("nfl_restarting",urlencode("true"),$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('NFL Team Updated',{themeState:'success'});</script>";
}

if (isset($_POST['updateTouchdownSequence'])) { 
  $touchdownSequence = trim($_POST['touchdownSequence']);
  WriteSettingToFile("touchdownSequence",$touchdownSequence,$pluginName);
  WriteSettingToFile("nfl_enabled",urlencode("false"),$pluginName);
  WriteSettingToFile("nfl_restarting",urlencode("true"),$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('Touchdown Sequence Updated',{themeState:'success'});</script>";
}

if (isset($_POST['updateWinSequence'])) { 
  $winSequence = trim($_POST['winSequence']);
  WriteSettingToFile("winSequence",$winSequence,$pluginName);
  WriteSettingToFile("nfl_enabled",urlencode("false"),$pluginName);
  WriteSettingToFile("nfl_restarting",urlencode("true"),$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('NFL Team Updated',{themeState:'success'});</script>";
}

if($touchdownEnabled) {
  $touchdownYes = "btn-primary";
  $touchdownNo = "btn-secondary";
}else {
  $touchdownYes = "btn-secondary";
  $touchdownNo = "btn-primary";
}

if (isset($_POST['touchdownEnabledYes'])) {
  $touchdownYes = "btn-primary";
  $touchdownNo = "btn-secondary";
  WriteSettingToFile("touchdownEnabled",urlencode("true"),$pluginName);
  WriteSettingToFile("nfl_enabled",urlencode("false"),$pluginName);
  WriteSettingToFile("nfl_restarting",urlencode("true"),$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('Touchdown Sequence On',{themeState:'success'});</script>";
}

if (isset($_POST['touchdownEnabledNo'])) {
  $touchdownYes = "btn-primary";
  $touchdownNo = "btn-secondary";
  WriteSettingToFile("touchdownEnabled",urlencode("false"),$pluginName);
  WriteSettingToFile("nfl_enabled",urlencode("false"),$pluginName);
  WriteSettingToFile("nfl_restarting",urlencode("true"),$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('Touchdown Sequence Off',{themeState:'success'});</script>";
}

if($winEnabled) {
  $winYes = "btn-primary";
  $winNo = "btn-secondary";
}else {
  $winYes = "btn-secondary";
  $winNo = "btn-primary";
}

if (isset($_POST['winEnabledYes'])) {
  $winYes = "btn-primary";
  $winNo = "btn-secondary";
  WriteSettingToFile("winEnabled",urlencode("true"),$pluginName);
  WriteSettingToFile("nfl_enabled",urlencode("false"),$pluginName);
  WriteSettingToFile("nfl_restarting",urlencode("true"),$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('Win Sequence On',{themeState:'success'});</script>";
}

if (isset($_POST['winEnabledNo'])) {
  $winYes = "btn-primary";
  $winNo = "btn-secondary";
  WriteSettingToFile("winEnabled",urlencode("false"),$pluginName);
  WriteSettingToFile("nfl_enabled",urlencode("false"),$pluginName);
  WriteSettingToFile("nfl_restarting",urlencode("true"),$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('Win Sequence Off',{themeState:'success'});</script>";
}


$sequenceDirectory= $settings['sequenceDirectory'];
$touchdownOptions = "";
$winOptions = "";
if(is_dir($playlistDirectory)) {
  if ($dirTemp = opendir($playlistDirectory)){
    while (($fileRead = readdir($dirTemp)) !== false) {
      if (($fileRead == ".") || ($fileRead == "..")){
        continue;
      }
      $fileRead = pathinfo($fileRead, PATHINFO_FILENAME);
      if ($fileRead == $touchdownSequence) {
        $touchdownOptions .= "<option selected value=\"{$fileRead}\">{$fileRead}</option>";
      } elseif ($fileRead == $winSequence) {
        $winOptions .= "<option selected value=\"{$fileRead}\">{$fileRead}</option>";
      } else {
        $touchdownOptions .= "<option value=\"{$fileRead}\">{$fileRead}</option>";
        $winOptions .= "<option value=\"{$fileRead}\">{$fileRead}</option>";
      }
    }
    closedir($dirTemp);
  }
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
    a {
      color: #D65A31;
    }
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
    .card-body {
      background-color: rgba(59, 69, 84, 0);
    }
    .card-subtitle {
      font-size: .9rem;
    }
    .setting-item {
      padding-bottom: 2em;
    }
    .input-group {
      padding-top: .5em;
    }
    
    .hvr-underline-from-center {
      display: inline-block;
      vertical-align: middle;
      -webkit-transform: perspective(1px) translateZ(0);
      transform: perspective(1px) translateZ(0);
      box-shadow: 0 0 1px rgba(0, 0, 0, 0);
      position: relative;
      overflow: hidden;
    }
    .hvr-underline-from-center:before {
      content: "";
      position: absolute;
      z-index: -1;
      left: 51%;
      right: 51%;
      bottom: 0;
      background: #FFF;
      height: 4px;
      -webkit-transition-property: left, right;
      transition-property: left, right;
      -webkit-transition-duration: 0.3s;
      transition-duration: 0.3s;
      -webkit-transition-timing-function: ease-out;
      transition-timing-function: ease-out;
    }
    .hvr-underline-from-center:hover:before, .hvr-underline-from-center:focus:before, .hvr-underline-from-center:active:before {
      left: 0;
      right: 0;
    }
		#remoteRunning {
			color: #60F779;
		}
		#remoteStopped {
			color: #A72525;
		}
		#update {
      padding-bottom: 1em;
      font-weight: bold;
			color: #A72525;
		}
    #env {
      color: #A72525;
    }
    #warning {
      font-weight: bold;
      color: #A72525;
    }
    #restartNotice {
			font-weight: bold;
      color: #D65A31;
      visibility: hidden;
		}
  </style>
</head>
<body>
  <div class="container-fluid plugin-body">
    <div class="container-fluid" style="padding-top: 2em;">
      <div class="card">
        <div class="card-body"><div class="justify-content-md-center row" style="padding-bottom: 1em;">
          <div class="col-md-auto">
            <h1>NFL Touchdown Plugin</h1>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="card">
        <div class="card-body">
          <!-- NFL Team -->
          <div class="justify-content-md-center row setting-item">
            <div class="col-md-6">
							<div class="card-title h5">
								NFL Team
							</div>
							<div class="mb-2 text-muted card-subtitle h6">
								Select your NFL team
							</div>
						</div>
            <div class="col-md-6">
              <form method="post">
                <div class="input-group">
                  <select class="form-select" id="teamID" name="teamID">
                    <option selected value=""></option>
                    <option value="ARI"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Arizona Cardinals</option>
                    <option value="ATL"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Atlanta Falcons</option>
                    <option value="BAL"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Baltimore Ravens</option>
                    <option value="BUF"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Buffalo Bills</option>
                    <option value="CAR"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Carolina Panthers</option>
                    <option value="CHI"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Chicago Bears</option>
                    <option value="CIN"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Cincinnati Bengals</option>
                    <option value="CLE"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Cleveland Browns</option>
                    <option value="DAL"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Dallas Cowboys</option>
                    <option value="DEN"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Denver Broncos</option>
                    <option value="DET"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Detroit Lions</option>
                    <option value="GB"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Green Bay Packers</option>
                    <option value="HOU"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Houston Texans</option>
                    <option value="IND"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Indianapolis Colts</option>
                    <option value="JAX"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Jacksonville Jaguars</option>
                    <option value="KC"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Kansas City Chiefs</option>
                    <option value="LAC"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Los Angeles Chargers</option>
                    <option value="LAR"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Los Angeles Rams</option>
                    <option value="LV"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Las Vegas Raiders</option>
                    <option value="MIA"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Miami Dolphins</option>
                    <option value="MIN"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Minnesota Vikings</option>
                    <option value="NE"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>New England Patriots</option>
                    <option value="NO"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>New Orleans Saints</option>
                    <option value="NYG"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>New York Giants</option>
                    <option value="NYJ"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>New York Jets</option>
                    <option value="PHI"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Philadelphia Eagles</option>
                    <option value="PIT"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Pittsburgh Steelers</option>
                    <option value="SEA"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Seattle Seahawks</option>
                    <option value="SF"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>San Francisco 49ers</option>
                    <option value="TB"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Tampa Bay Buccaneers</option>
                    <option value="TEN"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Tennessee Titans</option>
                    <option value="WSH"<?php if ($teamID == "ARI") { echo ' selected'; } ?>>Washington Commanders</option>
                  </select>
                  <span class="input-group-btn">
                    <button id="updateTeamID" name="updateTeamID" class="btn mr-md-3 hvr-underline-from-center btn-primary" type="submit">Update</button>
                  </span>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
</body>
</html>