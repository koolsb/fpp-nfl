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
				<div class="justify-content-md-center row pt-4">
					<div class="col-md-auto">
						<h3>Game Status</h3>
							<div style= "<?=$showDisabledDiv?>color:red;">
								Notice: Plugin is disabled
							</div>																
					</div>          
				</div>
				<div class="justify-content-md-center row">
					<?php if ($nflTeamID != '') { ?>
					<div class="col-6 py-5">
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
								<?php if ($nflStart == "0") {
									echo 'No game scheduled this week';
								} else {
									$nflStart = new DateTime($nflStart, new DateTimeZone("UTC"));
									$nflStart->setTimezone(new DateTimeZone(date_default_timezone_get()));
									echo $nflStart->format("l, F j @ g:i A");
								} ?>
								</div>
							</div>
						</div>
						<?php if (!in_array($nflStart, array("0", "1"))) { ?>
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
									<?=$nflTeamAbbreviation?> Score:
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
									<?=$nflOppoAbbreviation?> Score:
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
					<?php } if ($ncaaTeamID != '') { ?>
					<div class="col-6 py-5">
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
								<?php if ($ncaaStart == "0") {
									echo 'No game scheduled this week';
								} else {
									$ncaaStart = new DateTime($ncaaStart, new DateTimeZone("UTC"));
									$ncaaStart->setTimezone(new DateTimeZone(date_default_timezone_get()));
									echo $ncaaStart->format("l, F j @ g:i A");
								} ?>
								</div>
							</div>
						</div>
						<?php if (!in_array($ncaaStart, array("0", "1"))) { ?>
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
									<?=$ncaaOppoAbbreviation?> Score:
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
					<?php } if ($nhlTeamID != '') { ?>
					<div class="col-6 py-5">
						<div  style= "height:100; width:100; margin:auto">
							<img id="logoImage" src="<?echo $nhlTeamLogo;?>" width="100" height ="100">
						</div>	
						<div class="justify-content-md-center row pt-4">
							<div class="col-md-4">
								<div class="card-title h5">
									Puck Drop:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
								<?php if ($nhlStart == "0") {
									echo 'No game scheduled this week';
								} else {
									$nhlStart = new DateTime($nhlStart, new DateTimeZone("UTC"));
									$nhlStart->setTimezone(new DateTimeZone(date_default_timezone_get()));
									echo $nhlStart->format("l, F j @ g:i A");
								} ?>
								</div>
							</div>
						</div>
						<?php if (!in_array($nhlStart, array("0", "1"))) { ?>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									Opponent:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=$nhlOppoName?>
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
									<?php if ($nhlGameStatus == "pre") {
										echo "Pregame";
									} elseif ($nhlGameStatus == "in") { 
										echo "Playing";
									} elseif ($nhlGameStatus == "post") {
										echo "Postgame";
									} ?>
								</div>
							</div>
						</div>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									<?=$nhlTeamAbbreviation?> Score:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=$nhlMyScore?>
								</div>
							</div>
						</div>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									<?=$nhlOppoAbbreviation?> Score:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=$nhlOppoScore?>
								</div>
							</div>
						</div>
						<?php } ?>
					</div>
					<?php } if ($mlbTeamID != '') { ?>
					<div class="col-6 py-5">
						<div  style= "height:100; width:100; margin:auto">
							<img id="logoImage" src="<?echo $mlbTeamLogo;?>" width="100" height ="100">
						</div>	
						<div class="justify-content-md-center row pt-4">
							<div class="col-md-4">
								<div class="card-title h5">
									First Pitch:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
								<?php if ($mlbStart == "0") {
									echo 'No game scheduled this week';
								} else {
									$mlbStart = new DateTime($mlbStart, new DateTimeZone("UTC"));
									$mlbStart->setTimezone(new DateTimeZone(date_default_timezone_get()));
									echo $mlbStart->format("l, F j @ g:i A");
								} ?>
								</div>
							</div>
						</div>
						<?php if (!in_array($mlbStart, array("0", "1"))) { ?>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									Opponent:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=$mlbOppoName?>
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
									<?php if ($mlbGameStatus == "pre") {
										echo "Pregame";
									} elseif ($mlbGameStatus == "in") { 
										echo "Playing";
									} elseif ($mlbGameStatus == "post") {
										echo "Postgame";
									} ?>
								</div>
							</div>
						</div>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									<?=$mlbTeamAbbreviation?> Score:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=$mlbMyScore?>
								</div>
							</div>
						</div>
						<div class="justify-content-md-center row">
							<div class="col-md-4">
								<div class="card-title h5">
									<?=$mlbOppoAbbreviation?> Score:
								</div>
							</div>
							<div class="col-md-7">
								<div class="card-title">
									<?=$mlbOppoScore?>
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