<?php

/**
 * HiveSQL Helper
 *
 * This file is made to fetch data from HiveSQL status page
 *
 * @category   Helpers
 * @package    NetStat
 * @author     Florent Kosmala <kosflorent@gmail.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GPL-3.0
 */

declare(strict_types=1);

namespace NetStat\Helpers;

use PHPHtmlParser\Dom;

final class HiveSql
{
    /**
     * This var contains the full URL of HiveSQL status page
     *
     * @var string $server
     */
    public string $server = 'https://hive.arcange.eu/sqlstatus/';

    /**
     * GetData() function
     * Fetch the HTML page and extract status data
     *
     * @return array $data
     */
    public function getData(): array
    {
        $dom = new Dom();
        $dom->loadFromUrl($this->server);

        $sqlStatus = $dom->find('center', 0)->find('.panel-info',0);

        if ($sqlStatus) {
            $lastBcBlock = trim($sqlStatus->find('div', 1)->text);
            $dbBlockString = trim($sqlStatus->find('div', 2)->text);
            $dbBlock = explode('-', $dbBlockString);
            $lastDbBlock = trim($dbBlock[0]);
            $dbTime = trim($dbBlock[1]) . '-' . $dbBlock[2] . '-' . $dbBlock[3];
            $syncGap = trim($sqlStatus->find('div', 3)->text);

            $data = [
                "status" => "online",
                "last_blockchain_block" => $lastBcBlock,
                "last_database_block" => $lastDbBlock,
                "last_database_sync" => $dbTime,
                "sync_gap" => $syncGap
            ];
            return $data;
        }

        $data = [
            "status"=> 'offline',
            'error' => true,
            'message' => 'There is a problem to fetch HiveSQL status'
        ];
        return $data;
    }
}
