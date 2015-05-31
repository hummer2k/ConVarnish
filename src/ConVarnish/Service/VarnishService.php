<?php

namespace ConVarnish\Service;

use Zend\Stdlib\ArrayUtils;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class VarnishService
{
    const VARNISH_HEADER_HOST = 'X-Purge-Host';
    const VARNISH_HEADER_URL  = 'X-Purge-URL';
    const VARNISH_HEADER_TAGS = 'X-Purge-Tags';

    /**
     *
     * @var array
     */
    protected $servers;

    /**
     *
     * @param array $servers
     */
    public function __construct(array $servers)
    {
        $this->servers = $servers;
    }

    /**
     * discard url from varnish cache
     *
     * @param string $hostname
     * @return mixed curlinfo or false if error
     */
    public function purge($hostname, $purgeUrl = '/', $debug = false)
    {
        return $this->ban($hostname, $this->normalizePurgeUrl($purgeUrl), self::VARNISH_HEADER_URL, $debug);
    }

    /**
     * clean
     *
     * @param string $hostname
     * @param boolean $debug
     * @return array curl responses
     */
    public function clean($hostname, $debug = false)
    {
        return $this->ban($hostname, '.*', self::VARNISH_HEADER_URL, $debug);
    }

    /**
     * main method to add bans to varnish's ban list
     *
     * @param string $hostname
     * @param string $pattern PCRE pattern
     * @param string $type purge type header e.g. X-Purge-URL or X-Purge-Tags
     * @param boolean $debug show debug info
     * @return array curl responses
     */
    public function ban($hostname, $pattern = '.*', $type = self::VARNISH_HEADER_TAGS, $debug = false)
    {
        $info = array();
        foreach ($this->servers as $serverName => $server) {
            $finalUrl = $this->prepareFinalUrl($server);
            $curlOptionList = $this->getCurlOptions(array(
                CURLOPT_URL => $finalUrl,
                CURLOPT_HTTPHEADER => array(
                    self::VARNISH_HEADER_HOST . ': ' . '^('.str_replace('.', '.', $hostname).')$',
                    $type . ': ' . (empty($pattern) ? '.*' : $pattern)
                )
            ));

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
     * normalize regex
     *
     * @param string $purgeUrl
     * @return string
     */
    protected function normalizePurgeUrl($purgeUrl)
    {
        return '^/' . ltrim($purgeUrl, '/') . '$';
    }

    /**
     * retrieve varnish url for purge/ban, eg. http://127.0.0.1:80/
     *
     * @param array $server
     * @return string
     */
    protected function prepareFinalUrl(array $server)
    {
        $finalUrl = sprintf(
            'http://%s:%d%s',
            $server['ip'],
            $server['port'],
            '/'
        );
        return $finalUrl;
    }

    /**
     * retrieve default curl options
     *
     * @return array
     */
    protected function getCurlDefaultOptions()
    {
         $curlOptionList = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PURGE',
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_CONNECTTIMEOUT_MS => 2000
        );
        return $curlOptionList;
    }

    /**
     * retrieve options merged with default options
     *
     * @param array $options
     * @return array
     */
    protected function getCurlOptions(array $options = array())
    {
        return ArrayUtils::merge(
            $this->getCurlDefaultOptions(),
            $options
        );
    }
}
