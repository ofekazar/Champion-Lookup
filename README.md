# Champion-Lookup
This repository contains the source code for the site <championlookup.com>

#### API_KEY should be inserted in riot_api/CONSTS.php

## How does it work
This code compares ones champion mastery data to a database and recommand champions based to the champion mastery data alone.
The code used to create the table is database_scripts/init_table.php.
The code used to create a sample database using challanger and master summoners (ugly code be warned, it was created just as an example)  database_scripts/load_data.php


### First algorithm - Fill the database
Lets say we have 4 champions a, b, c and d an they have 400, 200, 100, 50 points respectively

a    b    c    d
400  200  100  50

Start from the first entry, *a*. Iterate on the entire array while skipping *a* and compare the values of the iteration with *a*.
```php
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
```

For each following value of *a* use this formola to add points to that champion in *a*'s' column
($innerChampionMastery / $outerChampionMastery) * 100 * ($outerChampionMastery / $summonerTopChampion)

For *a* it will look like that,
	First the inner champion is b
		b / a * 100 * a / a
		0.5 * 100 * 1 = 50
	Second the inner is c
		c / a * 100 * a / a
		0.25 * 100 * 1 = 25
	The last one is d
		d / a * 100 * a / a
		0.125 * 100 * 1 = 12.5

Those values will be added as key => value pairs in a column

Then for *b* we do the same
	First a
		a / b * 100 * b / a
		2 * 100 * 0.5 = 100
	Second c (remmember we skip the outer champion)
		c / b * 100 * b / a
		0.5 * 100 * 0.5 = 25
	Third d
		d / b * 100 * b / a
		0.25 * 100 * 0.5 = 12.5

And so on..

This data get collected from thousands of playes and them pushed to a database.

Basicly what this code is doing is chaking what is the most common champion to follow up a certian other champion.

### Second algorithm - compare summoner to the data base
First we acquire the summoner top N champion mastery data.
Then we SELECT from the database data about those champions, we format all the following champions data, adding it togther and sorting it in to an array.
For the last part we just filter champions that the summoner often play or played in the last T time and give out the top X champion that got left as result.

This is the code used to format the data:
```php
/**
 * Format the data output from get_champions_points to a more managable format.
 *
 * @param $championsPointsTable The dictonary output from get_champions_points
 * @param $masteryData The summoner mastery data
 * @param $masteryData The summoner mastery data in row form
 * @return Returns an array of championId => points pairs for comparison.
 */
public function format_champions_points_data($championsPointsTable, $masteryData, $masteryDataRow) {
	$championsPoints = array();
	$topChampionPoints = $masteryDataRow[0]["championPoints"];

	// Proccess data of each row
	while($row = $championsPointsTable->fetch_assoc()) {
		$cmp0 = $row["cmp0"];
		$pnt0 = $row["pnt0"];
		for ($i = 0; $i < $GLOBALS["DATABASE_ROW_SIZE"]; $i++) {
			$cmp = $row["cmp".$i];
			$pnt = $row["pnt".$i];

			if (array_key_exists($cmp, $championsPoints)) {
				$championsPoints[$cmp] += intval(($pnt / $pnt0) * 100 * ($masteryData[$row["id"]] / $topChampionPoints));
			}
			else {
				$championsPoints[$cmp] = intval(($pnt / $pnt0) * 100 * ($masteryData[$row["id"]] / $topChampionPoints));
			}
		}
	}
	if (!arsort($championsPoints)) {
		return NULL;
	}
	return $championsPoints;
}
```

It uses exacly the same formola as before.


The data then presented using html and css.
