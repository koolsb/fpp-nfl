<?php
include_once "/opt/fpp/www/common.php";
$pluginName = basename(dirname(__FILE__));
$pluginConfigFile = $settings['configDirectory'] ."/plugin." .$pluginName;
    
if (file_exists($pluginConfigFile)) {
  $pluginSettings = parse_ini_file($pluginConfigFile);
}
foreach ($pluginSettings as $key => $value) { 
  ${$key} = urldecode($value);
}
//set defaults if nothing saved
if (strlen(urldecode($pluginSettings['teamID']))<1){
  WriteSettingToFile("teamID",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['kickoff']))<1){
  WriteSettingToFile("kickoff",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['myScore']))<1){
  WriteSettingToFile("myScore",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['theirScore']))<1){
  WriteSettingToFile("theirScore",urlencode(""),$pluginName);
}

if (strlen(urldecode($pluginSettings['teamLogo']))<1){
  WriteSettingToFile("teamLogo",urlencode(""),$pluginName);
}

if (strlen(urldecode($pluginSettings['opponentID']))<1){
  WriteSettingToFile("opponentID",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['opponentID']))<1){
  WriteSettingToFile("ENABLED",urlencode("OFF"),$pluginName);
}
if (strlen(urldecode($pluginSettings['opponentName']))<1){
  WriteSettingToFile("opponentName",urlencode(""),$pluginName);
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
        <!-- Status -->
				<div class="justify-content-md-center row pt-4 pb-5">
					<div class="col-md-auto">
						<h3>Game Status</h3>
							<div style= "<? echo $showDisabledDiv; ?>color:red;">
								Notice: Plugin is disabled
							</div>																
					</div>          
				</div>
				<div class="justify-content-md-center row">
					<div class="col-md-2">
						<div class="card-title h5">
							Kickoff:
						</div>
					</div>
					<div class="col-md-3">
						<div class="card-title">
						<?php if ($kickoff == "0") {
							echo 'No game scheduled this week';
						} elseif ($kickoff == "1") {
							echo 'Your Team was Updated! Kickoff will display after next poll interval.';
						} else {
							$kickoff = new DateTime($kickoff, new DateTimeZone("UTC"));
							$kickoff->setTimezone(new DateTimeZone(date_default_timezone_get()));
							echo $kickoff->format("l, F j @ g:i A");
							//echo date_format(date_create($kickoff), "l, F j @ g:i A e") . date_default_timezone_get();
						} ?>
						</div>
					</div>
				</div>
				<?php if (!in_array($kickoff, array("0", "1"))) { ?>
				<div class="justify-content-md-center row">
					<div class="col-md-2">
						<div class="card-title h5">
							Opponent:
						</div>
					</div>
					<div class="col-md-3">
						<div class="card-title">
							<?=$opponentName?>
						</div>
					</div>
				</div>
				<div class="justify-content-md-center row pt-5">
					<div class="col-md-2">
						<div class="card-title h5">
							Game Status:
						</div>
					</div>
					<div class="col-md-3">
						<div class="card-title">
							<?php if ($gameStatus == "pre") {
								echo "Pregame";
							} elseif ($gameStatus == "in") { 
								echo "Playing";
							} elseif ($gameStatus == "post") {
								echo "Postgame";
							} ?>
						</div>
					</div>
				</div>
				<div class="justify-content-md-center row">
					<div class="col-md-2">
						<div class="card-title h5">
							<?=$teamID?> Score:
						</div>
					</div>
					<div class="col-md-3">
						<div class="card-title">
							<?=$myScore?>
						</div>
					</div>
				</div>
				<div class="justify-content-md-center row">
					<div class="col-md-2">
						<div class="card-title h5">
							<?=$opponentID?> Score:
						</div>
					</div>
					<div class="col-md-3">
						<div class="card-title">
							<?=$theirScore?>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>  
</body>
</html>