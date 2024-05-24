<?php

require __DIR__ . '/../vendor/autoload.php';

use Hive\PhpLib\HiveEngine\Blockchain as HiveEngine;

function pingDomain($domain){
    $starttime = microtime(true);
    $file      = fsockopen ($domain, 80, $errno, $errstr, 10);
    $stoptime  = microtime(true);
    $status    = 0;
 
    if (!$file) $status = -1;  // Site is down
    else {
        fclose($file);
        $status = ($stoptime - $starttime) * 1000;
        $status = floor($status);
    }
    return $status;
}

// Prepare the config array to test nodes
$config = [
    "debug" => false,
    "disableSsl" => false,
    "heNode" => "",
    "throwExceptions" => false
];

// Generate array of HiveEngine nodes
$nodesFile = __DIR__. '/../resources/hiveEngineNodes.json';
$list = file_get_contents($nodesFile);
$nodesList = json_decode($list, true);

$results = array();

foreach($nodesList as $node) {
    $nodeResult = array();
    $nodeResult['url'] = $node;
    $name = parse_url($node);
    $nodeResult['name'] = $name;

    // First get the ping
    if(strstr($node, '/')){
        $url = parse_url('https://'.$node, PHP_URL_HOST);
        $nodeResult['name'] = $url;
    } else {
        $nodeResult['name'] = $nodeResult['url'];
    }

    $online = pingDomain($nodeResult['name']);
    if ($online === -1) {
        $nodeResult['error'] = true;
        $nodeResult['online'] = false;
        $nodeResult['message'] = "This node is OFFLINE!";
    } else {
        $nodeResult['online'] = true;
        $nodeResult['ping'] = $online;
    }
    
    if (!isset($nodeResult['error'])) {
        $config["heNode"] = $node;
        $heBlockchain = new HiveEngine($config);
        $status = $heBlockchain->getStatus();

        if (empty($status)) {
            $nodeResult['error'] = true;
            $nodeResult['message'] = "The API can't return any data!";
        } else {
            $nodeResult['version'] = $status['SSCnodeVersion'];
            if (!empty($status['lightNode'])) {
                $nodeResult['nodeType'] = 'light';
            } else {
                $nodeResult['nodeType'] = 'full';
            }
            $nodeResult['chain'] = $status['chainId'];

        }
    }
    $results[] = $nodeResult;
}

// Sort by Ping
usort($results, function ($item1, $item2) {
    return $item1['ping'] <=> $item2['ping'];
});

// Push every error api at the end of array
$index = 0;
foreach ($results as $node) {
    if (isset($node['error'])) {
        $errorNode = $node;
        unset($results[$index]);
        array_push($results, $errorNode);
    }
    $index++;
}

$best = json_encode($results[0],JSON_PRETTY_PRINT);

$json = json_encode($results,JSON_PRETTY_PRINT);

$bestFile =  __DIR__. '/../resources/heBest.json';
$listFile = __DIR__. '/../resources/heList.json';

if (file_put_contents($bestFile, $best)) {
    'Best node file successfully written !';
} else {
    die('There was an error');
}

if (file_put_contents($listFile, $json)) {
    'Nodes list successfully written !';
} else {
    die('There was an error');
}