<?php

namespace ConVarnish\Service;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class VarnishService
{
    const VARNISH_HEADER_REGEX = 'X-Purge-Regex';
    const VARNISH_HEADER_HOST = 'X-Purge-Host';
    const VARNISH_HEADER_CONTENT_TYPE = 'X-Purge-Content-Type';
    
    /**
     * 
     * @param string $hostname
     * @return mixed curlinfo or false if error
     */
    public function purge($hostname, $purgeUrl, $port = 80, $debug = false)
    {
        $finalUrl = sprintf(
            'http://%s:%d%s', $hostname, $port, $purgeUrl
        );

        $curlOptionList = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PURGE',
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_URL => $finalUrl,
            CURLOPT_CONNECTTIMEOUT_MS => 2000
        );

        $fd = false;
        if ($debug == true) {
            print "\n---- Curl debug -----\n";
            $fd = fopen("php://output", 'w+');
            $curlOptionList[CURLOPT_VERBOSE] = true;
            $curlOptionList[CURLOPT_STDERR] = $fd;
        }
        $info = false;
        $curlHandler = curl_init();
        curl_setopt_array($curlHandler, $curlOptionList);
        curl_exec($curlHandler);
        if (!curl_error($curlHandler)) {
            $info = curl_getinfo($curlHandler);
        }
        curl_close($curlHandler);
        if ($fd !== false) {
            fclose($fd);
        }
        return $info;
    }
    
    
    
    /**
     * 
     * @param string $hostname
     * @param type $banUrl
     * @param type $port
     * @param type $debug
     * @return type
     */
    public function clean($hostname, $urlRegEx = '.*', $contentType = '.*', $port = 80, $debug = false)
    {
        $finalUrl = sprintf(
            'http://%s:%d%s', $hostname, $port, '/'
        );

        $curlOptionList = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PURGE',
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_URL => $finalUrl,
            CURLOPT_CONNECTTIMEOUT_MS => 2000,
            CURLOPT_HTTPHEADER => array(
                self::VARNISH_HEADER_HOST . ': ' . '^('.str_replace('.', '.', $hostname).')$',
                self::VARNISH_HEADER_REGEX . ': ' . (empty($urlRegEx) ? '.*' : $urlRegEx),
                self::VARNISH_HEADER_CONTENT_TYPE . ': ' . (empty($contentType) ? '.*' : $contentType)
            )
        );

        $fd = false;
        if ($debug == true) {
            print "\n---- Curl debug -----\n";
            $fd = fopen("php://output", 'w+');
            $curlOptionList[CURLOPT_VERBOSE] = true;
            $curlOptionList[CURLOPT_STDERR] = $fd;
        }
        
        $info = false;
        $curlHandler = curl_init();
        curl_setopt_array($curlHandler, $curlOptionList);
        
        curl_exec($curlHandler);
        if (!curl_error($curlHandler)) {
            $info = curl_getinfo($curlHandler);
        }
        curl_close($curlHandler);
        if ($fd !== false) {
            fclose($fd);
        }
        return $info;
    }
}
