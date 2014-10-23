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
    
    protected $servers;
    
    public function __construct(array $servers)
    {
        $this->servers = $servers;
    }
    
    /**
     * 
     * @param string $hostname
     * @return mixed curlinfo or false if error
     */
    public function purge($hostname, $purgeUrl, $debug = false)
    {
        $hostname = $this->xssCleaner($hostname);
        $purgeUrl = $this->xssCleaner($purgeUrl);
        $info = array();
        foreach ($this->servers as $serverName => $server) {
            $finalUrl = sprintf(
                'http://%s:%d%s', $server['ip'], $server['port'], $purgeUrl
            );
            $header = array(
                'Host: ' . $hostname, 
            );
            $curlOptionList = array(
                CURLOPT_URL                     => $finalUrl,
                CURLOPT_HTTPHEADER              => $header,
                CURLOPT_CUSTOMREQUEST           => "PURGE",
                CURLOPT_VERBOSE                 => true,
                CURLOPT_RETURNTRANSFER          => true,
                CURLOPT_NOBODY                  => true,
                CURLOPT_CONNECTTIMEOUT_MS       => 2000,
            );
            $fd = false;
            if ($debug == true) {
                print "\n---- Purge Output -----\n";
                $fd = fopen("php://output", 'w+');
                $curlOptionList[CURLOPT_VERBOSE] = true;
                $curlOptionList[CURLOPT_STDERR] = $fd;
            }

            $curlHandler = curl_init();
            curl_setopt_array($curlHandler, $curlOptionList);
            curl_exec($curlHandler);
            
            $error = curl_error($curlHandler);
            if (!$error) {
                $info[$serverName] = curl_getinfo($curlHandler);
                $info[$serverName]['success'] = true;
            } else {
                $info[$serverName]['error'] = $error;
                $info[$serverName]['success'] = false;
            }
            $info[$serverName]['purge_url'] = 'http://' . $hostname . $purgeUrl;

            curl_close($curlHandler);
            
            if ($fd !== false) {
                fclose($fd);
            }
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
    public function clean($hostname, $urlRegEx = '.*', $contentType = '.*', $debug = false)
    {
        $hostname = $this->xssCleaner($hostname);
        $info = array();
        foreach ($this->servers as $serverName => $server) {
            $finalUrl = sprintf(
                'http://%s:%d%s', $server['ip'], $server['port'], '/'
            );

            $curlOptionList = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'PURGE',
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true,
                CURLOPT_URL => $finalUrl,
                CURLOPT_CONNECTTIMEOUT_MS => 2000,
                CURLOPT_HTTPHEADER => array(
                    'Host: ' . $hostname,
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

            $curlHandler = curl_init();
            curl_setopt_array($curlHandler, $curlOptionList);

            curl_exec($curlHandler);
            $error = curl_error($curlHandler);
            if (!$error) {
                $info[$serverName] = curl_getinfo($curlHandler);
                $info[$serverName]['success'] = true;
            } else {
                $info[$serverName]['error'] = $error;
                $info[$serverName]['success'] = false;
            }
            curl_close($curlHandler);
            if ($fd !== false) {
                fclose($fd);
            }
        }
        return $info;
    }
    
    /**
     * Cross Site Script  & Code Injection Sanitization
     * 
     * @param type $input
     * @return type
     */
    protected function xssCleaner($input)
    {
            $returnStr = str_replace(array('<', ';', '|', '&', '>', "'", '"', ')', '('), array('&lt;', '&#58;', '&#124;', '&#38;', '&gt;', '&apos;', '&#x22;', '&#x29;', '&#x28;'), $input);
            $returnStr = str_ireplace('%3Cscript', '', $returnStr);
            return $returnStr;
    }
}
