<?php

/**
 * HiveEngine Helper
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

use Hive\PhpLib\HiveEngine\Blockchain as HeBlockchain;
use PhpPkg\Config\ConfigBox;

final class HiveEngine
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
     * @var string $heNodesFile
     */
    public string $heNodesFile = __DIR__ . '/../../resources/hiveEngineNodes.json';

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
        $list = file_get_contents($this->heNodesFile);
        $nodesList = (array) json_decode($list, true);

        return $nodesList;
    }

    /**
     * Use getStatus() function from Hive-Engine to get some data
     *
     * @param string $node URL of the selected node
     *
     * @return array $status
     */
    public function getBlockchainStatus(string $node): array
    {
        $settings = $this->settings;

        $settings['heNode'] = $node;
        $heBlockchain = new HeBlockchain($settings);
        $status = $heBlockchain->getStatus();
        return $status;
    }

    /**
     * Use getLatestBlockInfo() function from Hive-Engine to get last block data
     *
     * @param string $node URL of the selected node
     *
     * @return array $lastBlock
     */
    public function getLastBlock(string $node): array
    {
        $settings = $this->settings;

        $settings['heNode'] = $node;
        $heBlockchain = new HeBlockchain($settings);
        $lastBlock = $heBlockchain->getLatestBlockInfo();
        return $lastBlock;
    }

    /**
     * Use getBlockInfo() function from Hive-Engine to get selected block data
     *
     * @param string $node URL of the selected node
     * @param int $block Block number
     *
     * @return array $blockInfo
     */
    public function getBlock(string $node, int $block): array
    {
        $settings = $this->settings;

        $settings['heNode'] = $node;
        $heBlockchain = new HeBlockchain($settings);
        $blockInfo = $heBlockchain->getBlockInfo($block);
        return $blockInfo;
    }

    /**
     * Use getBlockInfo() function from Hive-Engine to get selected block data
     *
     * @param string $node URL of the selected node
     * @param string $txid Block number
     *
     * @return array $blockInfo
     */
    public function getTransaction(string $node, string $txid): array
    {
        $settings = $this->settings;

        $settings['heNode'] = $node;
        $heBlockchain = new HeBlockchain($settings);
        $txInfo = $heBlockchain->getTransactionInfo($txid);
        return $txInfo;
    }
}
