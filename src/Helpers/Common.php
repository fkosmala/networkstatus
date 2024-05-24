<?php

/**
 * Common Helper
 *
 * This file contains every common functions to ping / save / search
 *
 * @category   Helpers
 * @package    NetStat
 * @author     Florent Kosmala <kosflorent@gmail.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GPL-3.0
 */

declare(strict_types=1);

namespace NetStat\Helpers;

final class Common
{
    /**
     * Full path to the resources folder with every generated files
     *
     * @var string $filesFolder
     */
    private string $filesFolder = __DIR__ . '/../../resources/';

    /**
     * Ping selected domain and return his integer value
     * If domain is not online, this function return -1
     *
     * @param string $domain
     *
     * @return int $status
     */
    public function pingDomain(string $domain): int
    {
        if (parse_url($domain, PHP_URL_PORT)) {
            $port = parse_url($domain, PHP_URL_PORT);
        } else {
            $port = 443;
        }
        $starttime = microtime(true);
        $file = fsockopen($domain, $port, $errno, $errstr, 5);
        $stoptime = microtime(true);

        if (!$file) {
            $status = -1; // Site is down
        } else {
            fclose($file);
            $status = ($stoptime - $starttime) * 1000;
            $status = floor($status);
        }
        return (int)$status;
    }

    /**
     * Save data (from $data) in a file ($name)
     *
     * @param array $data
     * @param string $name
     *
     * @return void
     */
    public function saveFile(array $data, string $name): void
    {
        $target = $this->filesFolder . $name . '.json';
        $json = json_encode($data, JSON_PRETTY_PRINT);
        if (file_put_contents($target, $json)) {
            $msg = $name . ' file successfully written !';
        } else {
            $msg = 'There was an error for: ' . $name;
        }
        echo $msg;
    }

    /**
     * Search if $key exists in multi-dimentional $array
     * If $key exists, this function return true.
     *
     * @param string $key
     * @param array $array
     *
     * @return bool
     */
    public function multiArrayKeyExists(string $key, array $array): bool
    {
        if (array_key_exists($key, $array)) {
            return true;
        }
        foreach ($array as $element) {
            if (is_array($element)) {
                if ($this->multiArrayKeyExists($key, $element)) {
                    return true;
                }
            }
        }
        return false;
    }
}
