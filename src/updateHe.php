<?php

declare(strict_types=1);

namespace NetStat;

require __DIR__ . '/../vendor/autoload.php';

use NetStat\Helpers\Common;
use NetStat\Helpers\HiveEngine;

$common = new Common();

/*
 * Hive Engine nodes
 */

$engine = new HiveEngine();
// Generate array of HiveEngine nodes
$nodesList = $engine->getNodesList();

$results = array();

/** @var string $node */
foreach ($nodesList as $node) {
    $nodeResult = array();
    $nodeResult['url'] = $node;
    $nodeResult['name'] = parse_url($node);

    // Clean name
    if (strstr($node, '/')) {
        $url = parse_url('https://' . $node, PHP_URL_HOST);
        $nodeResult['name'] = $url;
    } else {
        $nodeResult['name'] = $nodeResult['url'];
    }

    // Ping test
    $online = $common->pingDomain($nodeResult['name']);
    if ($online === -1) {
        $nodeResult['error'] = true;
        $nodeResult['online'] = false;
        $nodeResult['message'] = "This node is OFFLINE!";
    } else {
        $nodeResult['online'] = true;
        $nodeResult['ping'] = $online;
    }

    unset($online);

    // If the ping had no error, continue tests
    if (!isset($nodeResult['error'])) {
        $nodeResult['processTime'] = array();

        $blockchainStart = microtime(true);
        // Test the getStatus function and fetch data
        $status = $engine->getBlockchainStatus($node);
        if (empty($status)) {
            $nodeResult['blockchain']['status']['error'] = true;
            $nodeResult['blockchain']['status']['message'] = "The API can't return any status data!";
        } else {
            $nodeResult['blockchain']['status']['version'] = (string) $status['SSCnodeVersion'];
            if (!empty($status['lightNode'])) {
                $nodeResult['blockchain']['status']['nodeType'] = 'light';
            } else {
                $nodeResult['blockchain']['status']['nodeType'] = 'full';
            }
            $nodeResult['blockchain']['status']['chain'] = (string) $status['chainId'];
        }

        // Test to fetch the last block data
        $lastBlock = $engine->getLastBlock($node);
        if (empty($lastBlock)) {
            $nodeResult['blockchain']['lastBlock']['error'] = true;
            $nodeResult['blockchain']['lastBlock']['message'] = "The API can't return last block data!";
        } else {
            $nodeResult['blockchain']['lastBlock']['timestamp'] = (string) $lastBlock['timestamp'];
            $nodeResult['blockchain']['lastBlock']['blockNumber'] = (int) $lastBlock['blockNumber'];
            $nodeResult['blockchain']['lastBlock']['refHiveBlockNumber'] = (int) $lastBlock['refHiveBlockNumber'];
            $nodeResult['blockchain']['lastBlock']['databaseHash'] = (string) $lastBlock['databaseHash'];
            $nodeResult['blockchain']['lastBlock']['hash'] = (string) $lastBlock['hash'];
            $nodeResult['blockchain']['lastBlock']['previousHash'] = (string) $lastBlock['previousHash'];
        }

        // Test to fetch selected block data (32948123)
        $selBlock = $engine->getBlock($node, 32948123);
        if (empty($selBlock)) {
            $nodeResult['blockchain']['selectedBlock']['error'] = true;
            $nodeResult['blockchain']['selectedBlock']['message'] = "The API can't return any block data!";
        } else {
            $nodeResult['blockchain']['selectedBlock']['timestamp'] = (string) $selBlock['timestamp'];
            $nodeResult['blockchain']['selectedBlock']['blockNumber'] = (int) $selBlock['blockNumber'];
            $nodeResult['blockchain']['selectedBlock']['refHiveBlockNumber'] = (int) $selBlock['refHiveBlockNumber'];
            $nodeResult['blockchain']['selectedBlock']['databaseHash'] = (string) $selBlock['databaseHash'];
            $nodeResult['blockchain']['selectedBlock']['previousHash'] = (string) $selBlock['previousHash'];
            $nodeResult['blockchain']['selectedBlock']['hash'] = (string) $selBlock['hash'];
        }

        // Test to fetch a transaction data (2c637ba2b83ad52415a9416452a72763e2f4d7ed)
        $selTx = $engine->getTransaction($node, "2c637ba2b83ad52415a9416452a72763e2f4d7ed");
        if (empty($selTx)) {
            $nodeResult['blockchain']['selectedTx']['error'] = true;
            $nodeResult['blockchain']['selectedTx']['message'] = "The API can't return any TX data!";
        } else {
            $nodeResult['blockchain']['selectedTx']['blockNumber'] = $selTx['blockNumber'];
            $nodeResult['blockchain']['selectedTx']['refHiveBlockNumber'] = $selTx['refHiveBlockNumber'];
            $nodeResult['blockchain']['selectedTx']['transactionId'] = $selTx['transactionId'];
            $nodeResult['blockchain']['selectedTx']['hash'] = $selTx['hash'];
        }

        $blockchainStop = microtime(true);

        $blockchainTime = ($blockchainStop - $blockchainStart) * 1000;
        $nodeResult['processTime']['blockchain'] = floor($blockchainTime);

        // remove vars to free memory (and avoid double data)
        unset($status);
        unset($lastBlock);
        unset($selBlock);
        unset($selTx);
    }
    $results[] = $nodeResult;
}

// Sort by Process
usort($results, function ($item1, $item2) {
    return $item1['processTime']['blockchain'] <=> $item2["processTime"]['blockchain'];
});

// Push every error api at the end of array
$index = 0;
foreach ($results as $nodeData) {
    if ($common->multiArrayKeyExists('error', $nodeData)) {
        $errorNode = $nodeData;
        unset($results[$index]);
        array_push($results, $errorNode);
    }
    $index++;
}

// Reset index from array
$results = array_merge($results);

//Save nodes list file
$common->saveFile($results, 'heList');

//Save best node file
$common->saveFile(reset($results), 'heBest');
