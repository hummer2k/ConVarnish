<?php

namespace ConVarnish\Service;

use Zend\Http\Client;
use Zend\Http\Response;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class VarnishService implements VarnishServiceInterface
{
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
     * @param string $type ban type header e.g. X-Ban-URL or X-Ban-Tags
     * @return Response[]
     */
    public function ban($hostname, $pattern = '.*', $type = self::VARNISH_HEADER_URL)
    {
        $info = array();
        foreach ($this->servers as $serverName => $server) {
            $uri = $this->prepareUri($server);
            $client = $this->getClient();
            $client->setUri($uri);
            $client->getRequest()->setAllowCustomMethods(true);
            $client->setMethod('BAN');
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
    public function banUri($hostname, $uri)
    {
        $pattern = $this->normalizeBanUrl($uri);
        return $this->ban($hostname, $pattern, self::VARNISH_HEADER_URL);
    }

    /**
     *
     * @param string $hostname
     * @param string|array $tags
     * @return Response[]
     */
    public function banTags($hostname, $tags)
    {
        if (is_array($tags)) {
            $tags = implode(',', $tags);
        }
        return $this->ban($hostname, $tags, self::VARNISH_HEADER_TAGS);
    }

    /**
     * normalize regex
     *
     * @param string $banUrl
     * @return string
     */
    protected function normalizeBanUrl($banUrl)
    {
        return '^/' . ltrim($banUrl, '/') . '$';
    }

    /**
     * retrieve varnish url for ban eg. http://127.0.0.1:80/
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
}
