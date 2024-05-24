<?php

/**
 * HiveEngine History Helper
 *
 * This file contains every functions to fetch data from Hive-Engine nodes
 *
 * @category   Helpers
 * @package    NetStat
 * @author     Florent Kosmala <kosflorent@gmail.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GPL-3.0
 */

declare(strict_types=1);

namespace NetStat\Helpers;

use Hive\PhpLib\HiveEngine\History as History;
use PhpPkg\Config\ConfigBox;

final class HeHistory
{
    /**
     * Full path to the config file
     *
     * @var string $configFile
     */
    public string $configFile = __DIR__ . '/../../config/default.json';

    /**
     * Full path to the Hive-engine nodes list file
     *
     * @var string $historyNodesFile
     */
    public string $historyNodesFile = __DIR__ . '/../../resources/heHistoryNodes.json';

    /**
     * Array with all settings from config file
     *
     * @var array $settings
     */
    public array $settings;

    /**
     * Constructor to get config file and create the settings array
     *
     * @return void
     */
    public function __construct()
    {
        $config = ConfigBox::new();
        $config->loadJsonFile($this->configFile);

        $this->settings = $config->getData();
    }

    /**
     * Get all the nodes from the nodes list file.
     *
     * @return array $nodesList
     */
    public function getNodesList(): array
    {
        $list = file_get_contents($this->historyNodesFile);
        $nodesList = (array) json_decode($list, true);

        return $nodesList;
    }

    /**
     * Function to fetch Hive Engine histore accountHistory() method
     *
     * @param string $node URL of the selected node
     * @param string $account account name to test the account history
     * @param int $limit Number of results
     *
     * @return array $status
     */
    public function getAccountHistory(string $node, string $account, int $limit = 10): array
    {
        $settings = $this->settings;

        $settings['heHistoryNode'] = $node;
        $history = new History($settings);
        $accHistory = $history->accountHistory($account, $limit);
        return $accHistory;
    }

    /**
     * Function to fetch Hive Engine histore accountHistory() method
     *
     * @param string $node URL of the selected node
     * @param string $symbol Symbol of the selected token
     *
     * @return array $status
     */
    public function getMarketHistory(string $node, string $symbol): array
    {
        $settings = $this->settings;

        $settings['heHistoryNode'] = $node;
        $history = new History($settings);
        $mktHistory = $history->marketHistory($symbol);
        return $mktHistory;
    }

    /**
     * Function to fetch Hive Engine histore accountHistory() method
     *
     * @param string $node URL of the selected node
     * @param array $nfts Array of NFT IDs
     * @param string $symbol Symbol of the selected token
     *
     * @return array $status
     */
    public function getNftHistory(string $node, array $nfts, string $symbol): array
    {
        $settings = $this->settings;

        $settings['heHistoryNode'] = $node;
        $history = new History($settings);
        $status = $history->nftHistory($nfts, $symbol);
        return $status;
    }
}
