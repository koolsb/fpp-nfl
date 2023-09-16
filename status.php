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

$showDisabledDiv="display:none;";

$pluginEnabled = $pluginSettings['ENABLED'];
if ($pluginEnabled=="OFF"){
	$showDisabledDiv	="display:block;";
}else{
	$showDisabledDiv ="display:none;";
}

//get active leagues
$activeLeagues = array();

foreach ($leagues as $league) {
	if (${$league . "TeamID"} != '') {
		array_push($activeLeagues, $league);
	}
}

?>

<!DOCTYPE html>
<html>
<head>
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
      background-color: rgba(255, 250, 84, 0.7);
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
        		<!-- Status -->
				<div class="justify-content-md-center row pt-4">
					<div class="col-md-auto">
						<h3>Game Status</h3>
							<div style= "<?=$showDisabledDiv?>color:red;">
								Notice: Plugin is disabled
							</div>																
					</div>          
				</div>
				<div class="justify-content-md-center row">
					<?php foreach($activeLeagues as $league) { ?>
						<div class="col-6 py-5">
						<div  style= "height:100; width:100; margin:auto">
							<img id="logoImage" src="<?echo ${$league . "TeamLogo"};?>" width="100" height ="100">
						</div>	
						<div class="justify-content-md-center row pt-4">
							<div class="col-md-4">
								<div class="card-title h5">
									<?php if ($league == "nfl" || $league == "ncaa") {
										echo "Kickoff:";
									} elseif ($league == "nhl") {
										echo "Puck Drop:";
									} elseif ($league == "mlb") {
										echo "First Pitch";
									} ?>
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
								<?php if (${$league . "Start"} == "0") {
									echo 'No game scheduled this week';
								} else {
									${$league . "Start"} = new DateTime(${$league . "Start"}, new DateTimeZone("UTC"));
									${$league . "Start"}->setTimezone(new DateTimeZone(date_default_timezone_get()));
									echo ${$league . "Start"}->format("l, F j @ g:i A");
								} ?>
								</div>
							</div>
						</div>
						<?php if (!in_array(${$league . "Start"}, array("0", "1"))) { ?>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									Opponent:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=${$league . "OppoName"}?>
								</div>
							</div>
						</div>
						<div class="justify-content-md-center row pt-5">
							<div class="col-md-4">
								<div class="card-title h5">
									Game Status:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?php if (${$league . "GameStatus"} == "pre") {
										echo "Pregame";
									} elseif (${$league . "GameStatus"} == "in") { 
										echo "Playing";
									} elseif (${$league . "GameStatus"} == "post") {
										echo "Postgame";
									} ?>
								</div>
							</div>
						</div>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									<?=${$league . "TeamAbbreviation"}?> Score:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=${$league . "MyScore"}?>
								</div>
							</div>
						</div>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									<?=${$league . "OppoAbbreviation"}?> Score:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=${$league . "OppoScore"}?>
								</div>
							</div>
						</div>
						<?php } ?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>  
</body>
</html>