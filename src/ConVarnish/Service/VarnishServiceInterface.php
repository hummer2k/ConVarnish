<?php

namespace ConVarnish\Service;

use Zend\Http\Response;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface VarnishServiceInterface
{
    const VARNISH_HEADER_HOST = 'X-Ban-Host';
    const VARNISH_HEADER_URL  = 'X-Ban-URL';
    const VARNISH_HEADER_TAGS = 'X-Ban-Tags';

    /**
     * main method to add bans to varnish's ban list
     *
     * @param string $hostname
     * @param string $pattern PCRE pattern
     * @param string $type ban type header e.g. X-Ban-URL or X-Ban-Tags
     * @return Response[]
     */
    public function ban($hostname, $pattern = '.*', $type = self::VARNISH_HEADER_URL);

    /**
     *
     * @param string $hostname
     * @param string $uri
     * @return Response[]
     */
    public function banUri($hostname, $uri);

    /**
     *
     * @param string $hostname
     * @param string|array $tags
     * @return Response[]
     */
    public function banTags($hostname, $tags);
}
