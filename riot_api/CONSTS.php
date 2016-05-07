<?php
	$API_KEY = {API-KEY};

	$SEC_IN_DAY = 86400;

	$LAST_PLAYED_FILTER = 3; //Filter out any champion that was played in the last N days (can be a float as well)

	$API_VIRSON = array(
			"summoner"    => "1.4",
			"league"      => "2.5",
			"static-data" => "1.2",
		);

	$REGIONS_LIST = array(
	 	'br'  ,
	 	'eune',
	    'euw' ,
	    'kr'  ,
	    'lan' ,
	    'las' ,
	    'na'  ,
	    'oce' ,
	    'jp'  ,
	    'ru'  ,
	    'tr'   
	);

	$PLATFORMS = array(
		 	'br'   => 'br1'  ,
		    'eune' => 'eun1' ,
		    'euw'  => 'euw1' ,
		    'kr'   => 'kr'   ,
		    'lan'  => 'la1'  ,
		    'las'  => 'la2'  ,
		    'na'   => 'na1'  ,
		    'oce'  => 'oc1'  ,
		    'jp'   => 'jp1'  ,
		    'ru'   => 'ru'   ,
		    'ty'   => 'tr1'  , 
	);

	$COUNT = 5; //The number of champions that are used to calculate the champions points, 3 - 7 is a good range with the one.

	$DATABASE_ROW_SIZE = 10; //The number of champion in each row. Unless changing the database this number should stay 10 (might hold less then 10 and ignore the rest).

	$FILTER_SIZE = 8; //The number of champions that will be ignored (if they have more the $FILTER_PRECENT precent of the top champion).
	$FILTER_PERCENT = 10; //Any champion less then this value will not be ignored even if it is in the FILTER_PRECENT range 

	$TOTAL_RESULTS = 4; //Can hold values between 1 to 4. The maximum number of results showen in the result page

	//MySQL Tabe information
	$SQL_SERVER = 'localhost';
	$SQL_DATABASE_NAME = 'champion_data';
	$SQL_USERNAME = 'ofekazDatabase';
	$SQL_PASSWORD = '_Agz_sg@UDq{';
	$SQL_POINTS_TABLE = "championPoints";

?>