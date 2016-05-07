<?php
	require_once '../riot_api/CONSTS.php';
	require_once '../riot_api/riot_api.php';

	class DatabaseHandler {
			private $sqlConnection;
			private $riotAPI;

			public function __construct($riotAPI, $conn) {
				$this->sqlConnection = $conn;
				$this->riotAPI = $riotAPI;
			}

			/**
			 * This function returns the all the rows of the found champions
			 * 
			 * @param $masteryData An array with champions id's as keys
			 * @param $tableName (String) The champions table name
			 * @return Returns a table of the champions given
			 */
			public function get_champions_points($masteryData, $tableName) {
				$query = "SELECT * FROM ".$tableName." WHERE id IN (";
				foreach ($masteryData as $championId => $championPoints) {
					$query = $query.$championId.", ";
				}
				$query = substr($query, 0, -2).")";

				return $this->sqlConnection->query($query);
			}

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
	}
?>