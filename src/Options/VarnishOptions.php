<?php

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish\Options;

use Laminas\Stdlib\AbstractOptions;

final class VarnishOptions extends AbstractOptions
{
    public const POLICY_ALLOW = 'allow';
    public const POLICY_DENY  = 'deny';

    /**
     * @var string
     */
    private $policy = self::POLICY_ALLOW;

    /**
     *
     * @var boolean
     */
    private $cacheEnabled = false;

    /**
     *
     * @var integer
     */
    private $defaultTtl = 14400;

    /**
     * @var array
     */
    private $cacheableActions = [];

    /**
     *
     * @var array
     */
    private $cacheableRoutes = [];

    /**
     *
     * @var boolean
     */
    private $debug = false;

    /**
     * varnish cache instances
     *
     * @var array
     */
    private $servers = [];

    /**
     *
     * @var bool
     */
    private $useEsi = false;

    /**
     * @var array
     */
    private $cachingStrategies = [];

    /**
     *
     * @return boolean
     */
    public function isCacheEnabled()
    {
        return (bool) $this->cacheEnabled;
    }

    /**
     *
     * @param boolean $flag
     * @return VarnishOptions
     */
    public function setCacheEnabled($flag)
    {
        $this->cacheEnabled = (bool) $flag;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getDefaultTtl()
    {
        return $this->defaultTtl;
    }

    /**
     *
     * @param integer $ttl
     * @return VarnishOptions
     */
    public function setDefaultTtl($ttl)
    {
        $this->defaultTtl = $ttl;
        return $this;
    }

    /**
     *
     * @param array $cacheableRoutes
     * @return VarnishOptions
     */
    public function setCacheableRoutes(array $cacheableRoutes)
    {
        $this->cacheableRoutes = array_reverse($cacheableRoutes);
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getCacheableRoutes()
    {
        return $this->cacheableRoutes;
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @param $debug
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @return array
     */
    public function getServers()
    {
        return $this->servers;
    }

    /**
     * @param array $servers
     * @return $this
     */
    public function setServers(array $servers)
    {
        $this->servers = $servers;
        return $this;
    }

    /**
     * @return bool
     */
    public function useEsi()
    {
        return $this->useEsi;
    }

    /**
     * @param $useEsi
     * @return $this
     */
    public function setUseEsi($useEsi)
    {
        $this->useEsi = $useEsi;
        return $this;
    }

    /**
     * @return array
     */
    public function getCacheableActions()
    {
        return $this->cacheableActions;
    }

    /**
     * @param array $cacheableActions
     * @return VarnishOptions
     */
    public function setCacheableActions($cacheableActions)
    {
        $this->cacheableActions = $cacheableActions;
        return $this;
    }

    /**
     * @return string
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * @param string $policy
     * @return VarnishOptions
     */
    public function setPolicy($policy)
    {
        $this->policy = $policy;
        return $this;
    }

    /**
     * @return array
     */
    public function getCachingStrategies()
    {
        return $this->cachingStrategies;
    }

    /**
     * @param array $cachingStrategies
     */
    public function setCachingStrategies(array $cachingStrategies)
    {
        $this->cachingStrategies = $cachingStrategies;
    }
}
