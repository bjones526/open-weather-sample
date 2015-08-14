<?php

$location = $_POST["input"];
$units = $_POST["unit"];
$lon = $_POST["lon"];
$lat = $_POST["lat"];
$zip = $_POST["zip"];
$country = $_POST["country"];

//determine which location input to use
if($lon && $lat){
	$location = "lat=".$lat.'&lon='.$lon;
}
else if($zip){
	//look up country code to pass to api call
	$str = file_get_contents('http://www.ils.unc.edu/~baileyja/code_sample/country_data.json');
	$countryData = json_decode($str);

	$matchIndex;
	foreach($countryData as $key => $val){
		if($val->name == $country){
			$matchIndex = $key;
		}
	}
	
	$country_code = $countryData[$matchIndex]->alpha2;
	
	$location = "zip=".$zip.",".$country_code;	
}
else{
	$location = 'q='.str_replace(' ', '', $location);//api quirk, remove whitespace
}

//determine temperature units
if($units == "celsius"){
	$units="metric";
}
else{
	$units = "imperial";
}	

//define call
$apiCall = 'api.openweathermap.org/data/2.5/weather?'.$location.'&units='.$units.'&&APPID=355e806e0c6b3aa9c5c24c36144d073b';

//retrieve weather data
$ch = curl_init($apiCall);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$weatherData = curl_exec($ch);
curl_close($ch);

//parse for JSON output
$jsonIn = json_decode($weatherData);

$selectedData = array("coord" => array("lon" => $jsonIn->coord->lon, "lat" => $jsonIn->coord->lat),
                                "weather" => array("conditions" => $jsonIn->weather[0]->description, "icon" => $jsonIn->weather[0]->icon),
                                "temp" =>array("current" => $jsonIn->main->temp, "min" => $jsonIn->main->temp_min, "max" => $jsonIn->main->temp_max),
                                "name" => $jsonIn->name,
                                "error" => $jsonIn->Error
                                );

$jsonOut = json_encode($selectedData);
echo $jsonOut;
?>
