<?php

declare(strict_types=1);

namespace NetStat;

require __DIR__ . '/../vendor/autoload.php';

use NetStat\Helpers\Common;
use NetStat\Helpers\HeHistory;

$common = new Common();

$heHistory = new HeHistory();

$nodesList = $heHistory->getNodesList();

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

    if (!isset($nodeResult['error'])) {
        $nodeResult['processTime'] = array();

        // Test Account history
        $accTimeStart = microtime(true);
        $accountResult = $heHistory->getAccountHistory($node, "bambukah", 10);
        if (empty($accountResult)) {
            $nodeResult['account']['error'] = true;
            $nodeResult['account']['message'] = "The API can't return accountHistory!";
        } else {
            $nodeResult['account']['success'] = true;
            $nodeResult['account']['message'] = "The API valid return accountHistory data!";
        }

        $accTimeStop = microtime(true);
        $accTime = ($accTimeStop - $accTimeStart) * 1000;


        // MarketHistory
        $mktTimeStart = microtime(true);
        $marketResult = $heHistory->getMarketHistory($node, 'BEE');
        if (empty($marketResult)) {
            $nodeResult['market']['error'] = true;
            $nodeResult['market']['message'] = "The API can't return marketHistory!";
        } else {
            $nodeResult['market']['success'] = true;
            $nodeResult['market']['message'] = "The API valid return marketHistory data!";
        }
        $mktTimeStop = microtime(true);
        $mktTime = ($mktTimeStop - $mktTimeStart) * 1000;


        // NftHistory
        $nftTimeStart = microtime(true);
        $nftResult = $heHistory->getNftHistory($node, [1,2], 'CITY');
        if (empty($nftResult)) {
            $nodeResult['nft']['error'] = true;
            $nodeResult['nft']['message'] = "The API can't return marketHistory!";
        } else {
            $nodeResult['nft']['success'] = true;
            $nodeResult['nft']['message'] = "The API return valid nftHistory data!";
        }
        $nftTimeStop = microtime(true);
        $nftTime = ($nftTimeStop - $nftTimeStart) * 1000;

        $nodeResult['processTime']['account'] = floor($accTime);
        $nodeResult['processTime']['market'] = floor($mktTime);
        $nodeResult['processTime']['nft'] = floor($nftTime);
        $nodeResult['processTime']['total'] = floor($accTime + $mktTime + $nftTime);

        unset($accountResult);
        unset($marketResult);
        unset($nftResult);
    }
    $results[] = $nodeResult;
}

$common->saveFile($results, 'heHistory');
