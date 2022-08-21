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

if (isset($_POST['updateTeamID'])) { 
  $teamID = trim($_POST['teamID']);
  WriteSettingToFile("teamID",$teamID,$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('NFL Team Updated',{themeState:'success'});</script>";
}

if (isset($_POST['updateTouchdownSequence'])) { 
  $touchdownSequence = trim($_POST['touchdownSequence']);
  WriteSettingToFile("touchdownSequence",$touchdownSequence,$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('Touchdown Sequence Updated',{themeState:'success'});</script>";
}

if (isset($_POST['updateFieldgoalSequence'])) { 
  $fieldgoalSequence = trim($_POST['fieldgoalSequence']);
  WriteSettingToFile("fieldgoalSequence",$fieldgoalSequence,$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('Fieldgoal Sequence Updated',{themeState:'success'});</script>";
}

if (isset($_POST['updateWinSequence'])) { 
  $winSequence = trim($_POST['winSequence']);
  WriteSettingToFile("winSequence",$winSequence,$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('Win Sequence Updated',{themeState:'success'});</script>";
}

if (isset($_POST['updateLogLevel'])) { 
  $logLevel = trim($_POST['logLevel']);
  WriteSettingToFile("logLevel",$logLevel,$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('Log Level Updated',{themeState:'success'});</script>";
}

//get available sequences
$url = "http://127.0.0.1/api/sequence/";
$options = array(
  'http' => array(
    'method'  => 'GET',
    )
);
$context = stream_context_create( $options );
$result = file_get_contents( $url, false, $context );
$sequences = json_decode($result, true);

//get NFL teams
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
            <form method="post">
              <div class="input-group">
                <select class="form-select" id="teamID" name="teamID">
                  <option selected value=""></option>
                  <?php foreach ($teams as $team) {
                          $team = $team['team'];
                          if ($team['abbreviation'] == $teamID) {
                            echo '<option selected value="' . $team['abbreviation'] . '">' . $team['displayName'] . '</option>';
                          } else {
                            echo '<option value="' . $team['abbreviation'] . '">' . $team['displayName'] . '</option>';
                          }
                  } ?>
                </select>
                <span class="input-group-btn">
                  <button id="updateTeamID" name="updateTeamID" class="btn mr-md-3 btn-dark" type="submit">Update</button>
                </span>
              </div>
            </form>
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
            <form method="post">
              <div class="input-group">
                <select class="form-select" id="touchdownSequence" name="touchdownSequence">
                  <option selected value=""></option>
                  <?php foreach ($sequences as $sequence) {
                          if ($sequence == $touchdownSequence) {
                            echo '<option selected value="' . $sequence . '">' . $sequence . '</option>';
                          } else {
                            echo '<option value="' . $sequence . '">' . $sequence . '</option>';
                          }
                          echo $sequence;
                  } ?>
                </select>
                <span class="input-group-btn">
                  <button id="updateTouchdownSequence" name="updateTouchdownSequence" class="btn mr-md-3 btn-dark" type="submit">Update</button>
                </span>
              </div>
            </form>
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
            <form method="post">
              <div class="input-group">
                <select class="form-select" id="fieldgoalSequence" name="fieldgoalSequence">
                  <option selected value=""></option>
                  <?php foreach ($sequences as $sequence) {
                          if ($sequence == $fieldgoalSequence) {
                            echo '<option selected value="' . $sequence . '">' . $sequence . '</option>';
                          } else {
                            echo '<option value="' . $sequence . '">' . $sequence . '</option>';
                          }
                          echo $sequence;
                  } ?>
                </select>
                <span class="input-group-btn">
                  <button id="updateFieldgoalSequence" name="updateFieldgoalSequence" class="btn mr-md-3 btn-dark" type="submit">Update</button>
                </span>
              </div>
            </form>
          </div>
        </div>
        <!-- Win Sequence -->
        <div class="justify-content-md-center row ">
          <div class="col-md-6">
            <div class="card-title h5">
              Win Sequence
            </div>
            <div class="mb-2 text-muted small h6">
              Select the sequence to play if your team wins<br>Select no sequence to disable
            </div>
          </div>
          <div class="col-md-6">
            <form method="post">
              <div class="input-group">
                <select class="form-select" id="winSequence" name="winSequence">
                  <option selected value=""></option>
                  <?php foreach ($sequences as $sequence) {
                          if ($sequence == $winSequence) {
                            echo '<option selected value="' . $sequence . '">' . $sequence . '</option>';
                          } else {
                            echo '<option value="' . $sequence . '">' . $sequence . '</option>';
                          }
                          echo $sequence;
                  } ?>
                </select>
                <span class="input-group-btn">
                  <button id="updateWinSequence" name="updateWinSequence" class="btn mr-md-3 btn-dark" type="submit">Update</button>
                </span>
              </div>
            </form>
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
            <form method="post">
              <div class="input-group">
                <select class="form-select" id="logLevel" name="logLevel">
                  <option <?php if ($logLevel = 4) { echo 'selected '; } ?>value="4">Info</option>
                  <option <?php if ($logLevel = 4) { echo 'selected '; } ?>value="5">Debug</option>
                </select>
                <span class="input-group-btn">
                  <button id="updateLogLevel" name="updateLogLevel" class="btn mr-md-3 btn-dark" type="submit">Update</button>
                </span>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
</body>
</html>