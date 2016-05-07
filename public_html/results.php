<!DOCTYPE html>
<html>
	<head>
		<link href='http://fonts.googleapis.com/css?family=Oswald:400,300' rel='stylesheet'>
		<link href='https://fonts.googleapis.com/css?family=Montserrat:400,500' rel='stylesheet' type='text/css'>
		<link href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css' rel="stylesheet">
		<link href="style.css" rel="stylesheet">
	</head>
	<body>
	    <div class="header">
	      <div class="container">
	        <div class="row">
	          <div class="col-xs-4 col-md-4">
	            <h1 class="site_title">Champion Lookup</h1>
	          </div>
	            <div class="col-xs-8 col-md-8">
	            <ul class="menu">
	              <li><a href="index.html">Home</a></li>
	              <li><a href="about.html">About</a></li>
	              <li><a href="contact.html">Contact Us</a></li>
	              <li><a href="updates.html">Updates</a></li>
	            </ul>
	          </div>
	        </div>
	      </div>
	    </div>

		<?php

			require_once '../riot_api/CONSTS.php';
			require_once '../riot_api/riot_api.php';
			require_once '../database_scripts/database_handler.php';

			$TIMEOUT = 10;
			$summonerName = htmlspecialchars(preg_replace('/\s+/', '', strtolower($_GET["summoner_name"])));
			$region = htmlspecialchars(preg_replace('/\s+/', '', strtolower($_GET["select_region"])));

			//Call where there is a possiblity that the user made a mistake.
			function failureState() {
				echo "<div class=\"container\">";
				echo "<div class=\"row\">";
				echo "<h2>Something Went Wrong</h2>";
				echo "<p>Please check that you spelled your summoner name right and picked the right region. <br/>Otherwise your account might be lacking mastery data.</p>";
				echo "</div>";
				echo "</div>";
				exit;
			}

			//Call when ever something that shouldn't fail does fail.
			function internalError() {
				echo "<div class=\"container\">";
				echo "<div class=\"row\">";
				echo "<h2>Something Went Wrong</h2>";
				echo "<p>Internal Error occurred, please try again or come back later! <br/> Possible couse is riot api server failed to responed to a request.</p>";
				echo "</div>";
				echo "</div>";
				exit;
			}

			$riotAPI = new RiotAPI($API_KEY);

			//Get data about the wanted summoner.
			$summonerData = $riotAPI->get_summoner_data($summonerName, $region, $TIMEOUT);

			//Output test for get_summoner_data
			if (is_null($summonerData)) {
				failureState();
			}

			$masteryDataRow = $riotAPI->get_mastery_data($summonerData[$summonerName]["id"], $region, $COUNT, $TIMEOUT);

			//Output test for get_mastery_data
			if (is_null($masteryDataRow)) {
				internalError();
			}

			$masteryData = $riotAPI->format_mastery_data($masteryDataRow);


			//Create sql connection
			$sqlConnection = new mysqli($SQL_SERVER, $SQL_USERNAME, $SQL_PASSWORD, $SQL_DATABASE_NAME);
			$dbHandler = new DatabaseHandler($riotAPI, $sqlConnection);

			if ($sqlConnection->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			}

			$championsPointsTable = $dbHandler->get_champions_points($masteryData, $SQL_POINTS_TABLE);

			if ($championsPointsTable->num_rows > 0) {
				$topChampionPoints = $masteryDataRow[0]["championPoints"];
				$lastPlayed = $riotAPI->get_champions_last_played_time($summonerData[$summonerName]["id"], $region, $COUNT, $TIMEOUT);

				if (is_null($lastPlayed)) {
					internalError();
				}

				$filterArray = $riotAPI->get_mastery_data($summonerData[$summonerName]["id"], $region, $FILTER_SIZE, $TIMEOUT);

				if (is_null($filterArray)) {
					internalError();
				}

				$filterArray = $riotAPI->format_mastery_data($filterArray);

				$championsPoints = $dbHandler->format_champions_points_data($championsPointsTable, $masteryData, $masteryDataRow);

				$shouldPlay = array();
				$i = 0;
				foreach($championsPoints as $championId => $points) {
					if ((!array_key_exists($championId, $filterArray) || ($topChampionPoints /$filterArray[$championId]) < $FILTER_PRECENT) && (!array_key_exists($championId, $lastPlayed) || $lastPlayed[$championId] > $LAST_PLAYED_FILTER)) {
						$championStaticData = $riotAPI->get_champion_static_data($championId, 1);

						if (is_null($championStaticData)) {
							internalError();
						}

						$shouldPlay[$championId] = array(
							"points" => $points,
							"name"   => $championStaticData["name"],
							"key"    => $championStaticData["key"],
							"title"  => $championStaticData["title"],
						);
						$i++;
					}

					if ($i === $TOTAL_RESULTS) {
						break;
					}
				}
 				
				if ($i === 0) {
					failureState();
				}

				$columns = 12 / $i;

				//Outputs data to html!
				echo "<div class=\"container\">";
				echo "<div class=\"row\">";
				echo "<h1 style=\"text-align: center;\">".$summonerData[$summonerName]["name"]."</h1>";
				echo "</div>";
				echo "</div>";

				echo "<div class=\"container\">";
				echo "<div class=\"row\">";
				foreach($shouldPlay as $championData) {
					echo "<div class=\"col-xs-".$columns." col-md-".$columns."\">";
					echo "<h2 style=\"text-align: center;\">".$championData["name"]."</h2>";
					echo "</div>";
				}
				echo "</div>";
				echo "</div>";

				echo "<div class=\"container\">";
				echo "<div class=\"row\">";
				foreach($shouldPlay as $championId => $championData) {
					echo "<div class=\"col-xs-".$columns." col-md-".$columns."\">";
					echo "<a href=\"links.php?id=".$championId."&name=".$championData["name"]."&key=".$championData["key"]."&title=".$championData["title"]."\"><img class=\"splash\" src=\"http://ddragon.leagueoflegends.com/cdn/img/champion/loading/".$championData["key"]."_0.jpg\" /></a>";
					echo "</div>";
				}
				echo "</div>";
				echo "</div>";

			}
			else {
				//Report Not Enough data to the user
				failureState();
			}
			$sqlConnection->close();
		?>
		
	</body>	
</html>