<?php

namespace carlansell94\UrlChecker;

use carlansell94\UrlChecker\{Formatter, FileHandler};

class Check
{
    private static int $max_requests = 150;
    /** @var array<int, bool> */
    private static array $curl_opts = array(
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY => true,
    );

    public static function setMaxRequests(int $requests): bool
    {
        if ($requests > 0) {
            self::$max_requests = $requests;
            return true;
        }

        return false;
    }

    /**
     * @return array<array<mixed>>|string
     */
    public static function fileUpload(): array|string|false
    {
        if (!isset($_FILES['file'])) {
            return "No file found";
        }

        if (!$loaded = FileHandler::load()) {
            return false;
        }

        if (!is_array($loaded)) {
            return $loaded;
        }

        return self::multiple($loaded);
    }

    /**
     * @return array<array<mixed>>|false
     */
    public static function single(string $url): array|false
    {
        if ($ch = curl_init($url)) {
            curl_setopt_array($ch, self::$curl_opts);
            curl_exec($ch);
            return [curl_getinfo($ch)];
        }

        return false;
    }

    /**
     * @param array<mixed> $urls
     * @return array<array<mixed>>
     */
    public static function multiple(array $urls): array
    {
        $request_id = 0;
        $responses = array();
        $urls = array_reverse($urls);

        $mh = curl_multi_init();
        $data_size = count($urls);
        $max_requests = self::$max_requests < $data_size ? self::$max_requests : $data_size;

        $add_handle = function ($id) use (&$urls, &$mh, &$responses) {
            $row = array_pop($urls);

            if (is_array($row)) {
                $responses[$id]['data'] = $row;
                $request_url = current($row);
            } else {
                $responses[$id] = array();
                $request_url = $row;
            }

            if ($ch = self::getHandle(strval($request_url), $id)) {
                curl_multi_add_handle($mh, $ch);
            }
        };

        for ($request_id; $request_id < $max_requests; $request_id++) {
            $add_handle($request_id);
        }

        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);

            while ($returned = curl_multi_info_read($mh)) {
                $id = curl_getinfo($returned['handle'], CURLINFO_PRIVATE);
                $responses[$id] += curl_getinfo($returned['handle']);
                curl_multi_remove_handle($mh, $returned['handle']);

                if ($urls) {
                    $add_handle($request_id);
                    $request_id++;
                }
            }
        } while ($running > 0);

        return $responses;
    }

    private static function getHandle(string $url, int|string $id = null): \CurlHandle|false
    {
        if (!$ch = curl_init($url)) {
            return false;
        }

        curl_setopt_array($ch, self::$curl_opts);

        if ($id !== null) {
            curl_setopt($ch, CURLOPT_PRIVATE, $id);
        }

        return $ch;
    }
}
