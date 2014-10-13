<?php
namespace ConVarnish\Service;

use Zend\Http\Client;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class VarnishService
{
    /**
     * 
     * @param string $uri
     * @return Zend\Http\Response
     */
    public function purge($hostname, $port, $purgeUrl, $debug = true)
    {        
        $finalUrl = sprintf(
            'http://%s:%d%s', $hostname, $port, $purgeUrl
        );
        
        $curlOptionList = array(
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_CUSTOMREQUEST     => 'PURGE',
            CURLOPT_HEADER            => true ,
            CURLOPT_NOBODY            => true,
            CURLOPT_URL               => $finalUrl,
            CURLOPT_CONNECTTIMEOUT_MS => 2000
        );

        $fd = false;
        if( $debug == true ) {
            print "\n---- Curl debug -----\n";
            $fd = fopen("php://output", 'w+');
            $curlOptionList[CURLOPT_VERBOSE] = true;
            $curlOptionList[CURLOPT_STDERR]  = $fd;
        }

        $curlHandler = curl_init();
        curl_setopt_array( $curlHandler, $curlOptionList );
        curl_exec( $curlHandler );
        curl_close( $curlHandler );
        if( $fd !== false ) {
            fclose( $fd );
        }
    }
}
