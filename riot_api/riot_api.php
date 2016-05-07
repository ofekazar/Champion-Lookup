<?php
	require_once './CONSTS.php';

	class RiotAPI {
		private $apiKey;

		public function __construct($apiKey) {
			$this->apiKey = $apiKey;
		}

		/**
		 * Sends a curl request to the server.
		 *
		 * @param $url (String) The request url.
		 * @return Returns the response from the server after json decode. 
		 */
		private function request($url) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = json_decode(curl_exec($ch), true);
			curl_close($ch);
			return $response;
		}

		/**
		 * This function repeats on a request if the request failed $timeout times (used to battle unexpected errors).
		 * If the request just doesn't work it will be stoped by the php timeout, this is unwanted so make sure to enter reasonable timeout.
		 *
		 * @param $url (String) The request url.
		 * @param $timeout (int) The number of times to repeat on the request (0 for infinity).
		 * @return Returns the response form the server after json decode. 
		 */
		private function timeout_request($url, $timeout) {
			$response = NULL;
			if ($timeout === 0 || $timeout < 0) {
				do {
					$response = $this->request($url);
				} while (array_key_exists('status', $response));
			}
			else {
				$counter = 0;
				do {
					$response = $this->request($url);
					$counter++;
				} while($counter < $timeout && array_key_exists('status', $response));

				if ($counter === $timeout && array_key_exists('status', $response)) {
					return NULL;
				}
			}

			return $response;
		}

		/**
		 * This funtion request data about a summoner by his summoner name.
		 *
		 * @param $summonerName (String)
		 * @param $region (String) The region initials (na, eune, euw... etc).
		 * @param $timeout (int) The number of times to retry the request if fails.
		 * @return Returns the summoner datain an array. First use the summoner name to get to his data $response[$summonerName]["id", "name"... etc].
		 *         Returns NULL when failes.
		 */
		public function get_summoner_data($summonerName, $region, $timeout = 10) {
			return $this->timeout_request('https://'.$region.'.api.pvp.net/api/lol/'.$region.'/v'.$GLOBALS["API_VIRSON"]["summoner"].'/summoner/by-name/'.$summonerName.'?api_key='.$this->apiKey, $timeout);
		}

		/**
		 * This funtion request data about a summoner champion mastery top champions
		 *
		 * @param $summonerId (int)
		 * @param $region (String) The region initials (na, eune, euw... etc).
		 * @param $count (int) The number of champions to request for.
		 * @param $timeout (int) The number of times to retry the request if fails.
		 * @return Returns the summoner mastery data with $count entries. Returns NULL when failes.
		 */
		public function get_mastery_data($summonerId, $region, $count = 3, $timeout = 10) {
			return $this->timeout_request('https://'.$region.'.api.pvp.net/championmastery/location/'.$GLOBALS["PLATFORMS"][$region].'/player/'.$summonerId.'/topchampions?count='.$count.'&api_key='.$this->apiKey,$timeout);
		}

		/**
		 * This funtion request data about a champion such as name and key.
		 *
		 * @param $championId (int)
		 * @param $timeout (int) The number of times to retry the request if fails.
		 * @return Returns the champion data. Returns NULL when failes.
		 */
		public function get_champion_static_data($championId, $timeout = 10) {
			return $this->timeout_request('https://global.api.pvp.net/api/lol/static-data/na/v'.$GLOBALS["API_VIRSON"]["static-data"].'/champion/'.$championId.'?api_key='.$this->apiKey, $timeout);
		} 

		/**
		 * This function format the row data form get_mastery_data to more managable $championId => $championPoints pair.
		 *
		 * @param $rowMasteryData (array) The return value of get_mastery_data.
		 * @param $timeout (int) The number of times to retry the request if fails.
		 * @return Returns the champion data.
		 */
		public function format_mastery_data($rowMasteryData) {
			$masteryData = array();
			foreach ($rowMasteryData as $championMastery) {
				$masteryData[$championMastery["championId"]] = $championMastery["championPoints"];
			}
			return $masteryData;
		}

		/**
		 * This function acquire and format data about the last time each champion was played for a give summoner.
		 * The last time played returned in days format
		 *
		 * @param $summonerId (int)
		 * @param $region (String) The region initials (na, eune, euw... etc).
		 * @param $timeout (int) The number of times to retry the request if fails.
		 * @return Returns pairs of $championId => $lastTimePlayed (in days).
		 */
		public function get_champions_last_played_time($summonerId, $region, $timeout = 10) {
			$response = $this->timeout_request('https://'.$region.'.api.pvp.net/championmastery/location/'.$GLOBALS["PLATFORMS"][$region].'/player/'.$summonerId.'/champions?api_key='.$this->apiKey, $timeout);
			if (is_null($response)) {
				return NULL;
			}
	
			return $this->format_last_played($response);
		}

		/**
		 * Formats the data from get_champions_last_played_time to $championId => $lastTimePlayed (in days) pairs.
		 */
		private function format_last_played($rowChampionsData) {
			$formatedData = array();
			$currentTime = time();
			foreach ($rowChampionsData as $championData) {
				$formatedData[$championData["championId"]] = ($currentTime - ($championData["lastPlayTime"] / 1000)) / $GLOBALS["SEC_IN_DAY"];
			}
			return $formatedData;
		}

		/**
		 * Get both ally and enemy tips on a champion
		 *
		 * @param $championId (int)
		 * @param $timeout (int) The number of times to retry the request if fails.
		 * @return Returns the champion information with the fields allytips and enemytips
		 */
		public function get_champion_tips($championId, $timeout = 10) {
			return $this->timeout_request("https://global.api.pvp.net/api/lol/static-data/eune/v".$GLOBALS["API_VIRSON"]["static-data"]."/champion/".$championId."?champData=allytips,enemytips&api_key=".$this->apiKey, $timeout);
		}
	}
?>