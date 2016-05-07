#!/usr/bin/php

<?php
	require_once '../riot_api/CONSTS.php'

	function request($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = json_decode(curl_exec($ch), true);
		curl_close($ch);
		return $response;
	}

	echo "Accuiring ids... \n";

	$summonersIds = array();
	$championDataArray = array();
	$longestArray = 0;
	foreach($REGIONS_LIST as $region) {
		do {
			$challengerArray = request('https://'.$region.'.api.pvp.net/api/lol/'.$region.'/v'.$API_VIRSON["league"].'/league/challenger?type=RANKED_SOLO_5x5&api_key='.$API_KEY);
		} while (array_key_exists('status', $challengerArray));
			
		do {
			$masterArray = request('https://'.$region.'.api.pvp.net/api/lol/'.$region.'/v'.$API_VIRSON["league"].'/league/master?type=RANKED_SOLO_5x5&api_key='.$API_KEY);
		} while (array_key_exists('status', $masterArray));
			
		$topSummonersArray = array_merge($challengerArray["entries"], $masterArray["entries"]);

		$i = 0;
		$summonersIds[$region] = array(0 => 0);
		foreach($topSummonersArray as $summoner) {
			$summonersIds[$region][] = $summoner["playerOrTeamId"];
			$summonersIds[$region][0] += 1;
			if ($i === 400) {
				break;
			}
			$i++;
		}

		if ($summonersIds[$region][0] > $longestArray) {
			$longestArray = $summonersIds[$region][0];
		}
	}

	echo "Reciving champion mastery information... \n";

	for ($i = 1; $i <= 400; $i++) {
		foreach($summonersIds as $region => $idsArray) {
			if ($idsArray[0] < $i) {
				unset($summonersIds[$region]);
				continue;
			}
			$timeout = 0;
			do {
				echo $i." ".$timeout." ".'https://'.$region.'.api.pvp.net/championmastery/location/'.$PLATFORMS[$region].'/player/'.$idsArray[$i].'/topchampions?count='.($DATABASE_ROW_SIZE + 1).'&api_key='.$API_KEY."\n";

				$summonerMasteryData=request('https://'.$region.'.api.pvp.net/championmastery/location/'.$PLATFORMS[$region].'/player/'.$idsArray[$i].'/topchampions?count='.($DATABASE_ROW_SIZE + 1).'&api_key='.$API_KEY);
				$timeout += 1;
				if ($timeout == 10) {
					echo "TIMEOUT\n";
					break;
				}
			} while (array_key_exists('status', $summonerMasteryData));

			if ($timeout == 10) {
				continue;
			}

			$summonerTopChampion = $summonerMasteryData[0];
			foreach ($summonerMasteryData as $outerChampionMastery) {
				if (!(array_key_exists($outerChampionMastery["championId"], $championDataArray))) {
					$championDataArray[$outerChampionMastery["championId"]] = array();
				}

				foreach ($summonerMasteryData as $innerChampionMastery) {
					if ($outerChampionMastery["championId"] === $innerChampionMastery["championId"]) {
						continue;
					}
					$addition = intval(($innerChampionMastery["championPoints"] / $outerChampionMastery["championPoints"]) * 100 * ($outerChampionMastery["championPoints"] / $summonerTopChampion["championPoints"]));

					if (array_key_exists($innerChampionMastery["championId"], $championDataArray[$outerChampionMastery["championId"]])) {
						$championDataArray[$outerChampionMastery["championId"]][$innerChampionMastery["championId"]] += $addition;
					}
					else {
						$championDataArray[$outerChampionMastery["championId"]][$innerChampionMastery["championId"]] = $addition;
					}
				}
			}
		}
	}

	echo "Filling database... \n";

	$sqlConnection = new mysqli($SQL_SERVER, $SQL_USERNAME, $SQL_PASSWORD, $SQL_DATABASE_NAME);

	if ($sqlConnection->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	foreach($championDataArray as $championId => $championRow) {
		if (!arsort($championRow)) {
			echo "Faile to sort champion row array, skipping champion ".$championId."\n";
			echo "Add champion mainually using this data\n".var_dump($championRow);
			continue;
		}

		$query = "INSERT INTO championPoints (id, cmp0, pnt0, cmp1, pnt1, cmp2, pnt2, cmp3, pnt3, cmp4, pnt4, cmp5, pnt5, cmp6, pnt6, cmp7, pnt7, cmp8, pnt8, cmp9, pnt9) VALUES ";
		$values = "(".$championId.", ";

		$i = 0;
		foreach ($championRow as $id => $points) {
			$values = $values.$id.", ".$points.", ";

			if ($i === ($DATABASE_ROW_SIZE - 1)) {
				break;
			}
			$i++;
		}
		$values = substr($values, 0, -2).")";
		$query = $query.$values;
		echo $query."\n";

		if ($sqlConnection->query($query) === TRUE) {
			echo $championId." record as been created successefully \n";
		}
		else {
			echo "Failed to create ".$championId." column"."\n";
		}
	}

	$sqlConnection->close();
?>