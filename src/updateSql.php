<?php

declare(strict_types=1);

namespace NetStat;

require __DIR__ . '/../vendor/autoload.php';

use NetStat\Helpers\Common;
use NetStat\Helpers\HiveSql;

$common = new Common();

$hivesql = new HiveSql();
$hivesqlResult = $hivesql->getData();
$common->saveFile($hivesqlResult, 'hiveSql');
