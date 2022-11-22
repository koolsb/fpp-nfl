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
if (strlen(urldecode($pluginSettings['nflTeamID']))<1){
  WriteSettingToFile("nflTeamID",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['nflKickoff']))<0){
  WriteSettingToFile("nflKickoff",urlencode("1"),$pluginName);
}
if (strlen(urldecode($pluginSettings['nflMyScore']))<1){
  WriteSettingToFile("nflMyScore",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['nflOppoScore']))<1){
  WriteSettingToFile("nflOppoScore",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['nflTeamLogo']))<1){
  WriteSettingToFile("nflTeamLogo",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['nflOppoID']))<1){
  WriteSettingToFile("nflOppoID",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['nflOppoName']))<1){
	WriteSettingToFile("nflOppoName",urlencode(""),$pluginName);
}	
if (strlen(urldecode($pluginSettings['ncaaTeamID']))<1){
	WriteSettingToFile("ncaaTeamID",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['ncaaTeamAbbreviation']))<1){
	WriteSettingToFile("ncaaTeamAbbreviation",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['ncaaKickoff']))<0){
	WriteSettingToFile("ncaaKickoff",urlencode("1"),$pluginName);
}
if (strlen(urldecode($pluginSettings['ncaaMyScore']))<1){
	WriteSettingToFile("ncaaMyScore",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['ncaaOppoScore']))<1){
	WriteSettingToFile("ncaaOppoScore",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['ncaaTeamLogo']))<1){
	WriteSettingToFile("ncaaTeamLogo",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['ncaaOppoID']))<1){
	WriteSettingToFile("ncaaOppoID",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['ncaaOppoName']))<1){
	WriteSettingToFile("ncaaOppoName",urlencode(""),$pluginName);
}
if (strlen(urldecode($pluginSettings['ENABLED']))<1){
  WriteSettingToFile("ENABLED",urlencode("OFF"),$pluginName);
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
        		<!-- Status -->
				<div class="justify-content-md-center row pt-4 pb-5">
					<div class="col-md-auto">
						<h3>Game Status</h3>
							<div style= "<?=$showDisabledDiv?>color:red;">
								Notice: Plugin is disabled
							</div>																
					</div>          
				</div>
				<div class="justify-content-md-center row">
					<div class="col-6">
						<div  style= "height:100; width:100; margin:auto">
							<img id="logoImage" src="<?echo $nflTeamLogo;?>" width="100" height ="100">
						</div>	
						<div class="justify-content-md-center row pt-4">
							<div class="col-md-4">
								<div class="card-title h5">
									Kickoff:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
								<?php if ($nflKickoff == "0") {
									echo 'No game scheduled this week';
								} elseif ($nflKickoff == "1") {
									echo 'Your Team was Updated! Kickoff will display after next poll interval.';
								} else {
									$nflKickoff = new DateTime($nflKickoff, new DateTimeZone("UTC"));
									$nflKickoff->setTimezone(new DateTimeZone(date_default_timezone_get()));
									echo $nflKickoff->format("l, F j @ g:i A");
								} ?>
								</div>
							</div>
						</div>
						<?php if (!in_array($nflKickoff, array("0", "1"))) { ?>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									Opponent:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=$nflOppoName?>
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
									<?php if ($nflGameStatus == "pre") {
										echo "Pregame";
									} elseif ($nflGameStatus == "in") { 
										echo "Playing";
									} elseif ($nflGameStatus == "post") {
										echo "Postgame";
									} ?>
								</div>
							</div>
						</div>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									<?=$nflTeamID?> Score:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=$nflMyScore?>
								</div>
							</div>
						</div>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									<?=$nflOppoID?> Score:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=$nflOppoScore?>
								</div>
							</div>
						</div>
						<?php } ?>
					</div>
					<div class="col-6">
						<div  style= "height:100; width:100; margin:auto">
							<img id="logoImage" src="<?echo $ncaaTeamLogo;?>" width="100" height ="100">
						</div>	
						<div class="justify-content-md-center row pt-4">
							<div class="col-md-4">
								<div class="card-title h5">
									Kickoff:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
								<?php if ($ncaaKickoff == "0") {
									echo 'No game scheduled this week';
								} elseif ($ncaaKickoff == "1") {
									echo 'Your Team was Updated! Kickoff will display after next poll interval.';
								} else {
									$ncaaKickoff = new DateTime($ncaaKickoff, new DateTimeZone("UTC"));
									$ncaaKickoff->setTimezone(new DateTimeZone(date_default_timezone_get()));
									echo $ncaaKickoff->format("l, F j @ g:i A");
								} ?>
								</div>
							</div>
						</div>
						<?php if (!in_array($ncaaKickoff, array("0", "1"))) { ?>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									Opponent:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=$ncaaOppoName?>
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
									<?php if ($ncaaGameStatus == "pre") {
										echo "Pregame";
									} elseif ($ncaaGameStatus == "in") { 
										echo "Playing";
									} elseif ($ncaaGameStatus == "post") {
										echo "Postgame";
									} ?>
								</div>
							</div>
						</div>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									<?=$ncaaTeamAbbreviation?> Score:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=$ncaaMyScore?>
								</div>
							</div>
						</div>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									<?=$ncaaOppoID?> Score:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=$ncaaOppoScore?>
								</div>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>  
</body>
</html>