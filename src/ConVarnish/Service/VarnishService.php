<?php

namespace ConVarnish\Service;

use Zend\Http\Client;
use Zend\Http\Response;

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
     * @var Client
     */
    protected $client;

    /**
     *
     * @param array $servers
     */
    public function __construct(array $servers)
    {
        $this->servers = $servers;
    }

    /**
     * main method to add bans to varnish's ban list
     *
     * @param string $hostname
     * @param string $pattern PCRE pattern
     * @param string $type purge type header e.g. X-Purge-URL or X-Purge-Tags
     * @return Response[]
     */
    public function purge($hostname, $pattern = '.*', $type = self::VARNISH_HEADER_URL)
    {
        $info = array();
        foreach ($this->servers as $serverName => $server) {
            $uri = $this->prepareUri($server);
            $client = $this->getClient();
            $client->setUri($uri);
            $client->getRequest()->setAllowCustomMethods(true);
            $client->setMethod('PURGE');
            $client->setHeaders([
                self::VARNISH_HEADER_HOST => '^('.$hostname.')$',
                $type => $pattern,
            ]);
            $info[$serverName] = $client->send();
        }
        return $info;
    }

    /**
     *
     * @param string $hostname
     * @param string $uri
     * @return Response[]
     */
    public function purgeUri($hostname, $uri)
    {
        $pattern = '^' . $uri . '$';
        return $this->purge($hostname, $pattern, self::VARNISH_HEADER_URL);
    }

    /**
     *
     * @param string $hostname
     * @param string|array $tags
     * @return Response[]
     */
    public function purgeTags($hostname, $tags)
    {
        if (is_array($tags)) {
            $tags = implode(',', $tags);
        }
        return $this->purge($hostname, $tags, self::VARNISH_HEADER_TAGS);
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
     * retrieve varnish url for purge eg. http://127.0.0.1:80/
     *
     * @param array $server
     * @return string
     */
    private function prepareUri(array $server)
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
     *
     * @return Client
     */
    public function getClient()
    {
        if (null === $this->client) {
            $this->client = new Client();
        }
        return $this->client;
    }

    /**
     *
     * @param Client $client
     * @return VarnishService
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }
}
