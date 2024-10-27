<?php
//CORS policy fixing. Security measures for web API stuff
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-with");
header("Content-Type: application/json; charset=UTF-8");
//Setting up initial OPTION request that the browser used to check the server
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    //Respond 200 to PreFlight
    http_response_code(200);
    exit();
};
//Set up main condition
if($_SERVER["REQUEST_METHOD"] === 'POST'){
    
    
    //Atlas set up
    $publicKey = "qsqrxezc";
    $privateKey = "b2ebf8bd-dd40-496c-9760-6dcd9a9e8182";
    $projectId = "671d943efea2cc61071604d9";
    $databaseName = "energyMuseum";
    $collectionName = "HackRU";
    
    //Headers for HTTP request to cloud database
    $headers = [
    "Content-Type: application/json",
    "Access-Control-Request-Headers: *",
    "api-key: $privateKey"
    ];
    
    $url = "https://data.mongodb-api.com/app/endpoint/data/v1/action/findOne";
    
    //read raw data send through HTTP request without having to loop through $_POST
    $dataSentPOST = file_get_contents('php://input');
    
    //Built in function that decodes string data into PHP code to be used 
    //$data = json_decode($dataSentPOST, true);
    //Okay so SPLINE sends data as one massive string not a JSON so we need to manually turn it into an array, I can force this into an associative but for not an index one will work
    
    $data = explode(",",$dataSentPOST);
    //process the data to see what im getting from the database
    
    if (strval($data[0]) === "New York") {
        $cityName = "New York";
    } 
    elseif (strval($data[0]) === "Miami") {
        $cityName = "Miami";
    } 
    elseif (strval($data[0]) === "Los Angeles") {
        $cityName = "Los Angeles";
    } 
    elseif (strval($data[0]) === "Chicago") {
        $cityName = "Chicago";
    } 
    elseif (strval($data[0]) === "Seattle") {
        $cityName = "Seattle";
    } 
    else {
        $cityName = "Something Went Wrong";
    }
    
    //Create the data to send to MongoDB
    $data = [
    "dataSource" => "Cluster93310",   
    "database" => $databaseName, 
    "collection" => $collectionName,
    "filter" => ["city" => $cityName]
    ];
    
    // Initialize cURL to make an HTTP request to MongoDB Atlas
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    // Execute the request and capture the response
    $response = curl_exec($ch);
    
    // Handle errors
    if ($response === false) {
        echo json_encode(["error" => "Request failed: " . curl_error($ch)]);
        curl_close($ch);
        exit;
    }
    /*
    else {
        echo json_encode(
            ["success" => "idk but it worked",
             "data" =>  json_encode($response),
             "cityName" => $cityName
            ]);
        curl_close($ch);
        exit;
    }
    */
    
    
    $jsonString = file_get_contents('ewqeqw.txt');
    $mongoData = json_decode($jsonString,true);
    
    /*
    if(true){
        echo json_encode(
            ["success" => "idk but it worked",
             "data" =>  json_decode($jsonString),
             "cityName" => $cityName
            ]);
        exit;
    }
    */
    
    //Decode data from mongo to JSON
    $mongoDataTRIAL = json_decode($jsonString,true);
    foreach($mongoDataTRIAL as $city){
        if($city['city'] === $cityName){
            $mongoData = $city;
        }
    }
    $cityReturned = $mongoData['city'];
    
    //Get just the internal array of energy usage
    $internalEnergyUsage = $mongoData['energyUsage'];
    $totalUsageSum = 0;
    foreach($internalEnergyUsage as $usagePart){
        $totalUsageSum += $usagePart;
    }
    
    $usageTotal = $totalUsageSum;
    $usageSolar = $internalEnergyUsage['solar'];
    $usageGas = $internalEnergyUsage['gas'];
    $usageHydro = $internalEnergyUsage['hydro'];
    $usageNuclear = $internalEnergyUsage['nuclear'];
    
    //Get the internal array oof average cost per each energy
    $internalAvgCost = $mongoData['averageCost'];
    $highestAvgSum = 0;
    foreach($internalEnergyUsage as $avgPart){
        if($avgPart > $highestAvgSum){
            $highestAvgSum = $avgPart;
        }
    }
    
    $avgHighest = $highestAvgSum;
    $avgSolar = $internalAvgCost['solar'];
    $avgGas = $internalAvgCost['gas'];
    $avgHydro = $internalAvgCost['hydro'];
    $avgNuclear = $internalAvgCost['nuclear'];
    
    //Grab peak time and the dominant power source 
    
    $peakTime = $mongoData['peakUsageTime'];
    $dominantPowerSource = $mongoData['dominantEnergySource'];
    
    
    $finalData = [
    "city" => $cityReturned,
    "usageTotal" => $usageTotal,
    "usageSolar" => $usageSolar,
    "usageGas" => $usageGas,
    "usageHydro" => $usageHydro,
    "usageNuclear" => $usageNuclear,
    "avgHighest" => $avgHighest,
    "avgSolar" => $avgSolar,
    "avgGas" => $avgGas,
    "avgHydro" => $avgHydro,
    "avgNuclear" => $avgNuclear,
    "peakUsageTime" => $peakTime,
    "dominantPowerSource" => $dominantPowerSource
    ];
    
    
    
    echo json_encode($finalData);
}
?>