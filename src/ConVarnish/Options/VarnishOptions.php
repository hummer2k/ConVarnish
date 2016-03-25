<?php
namespace ConVarnish\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class VarnishOptions extends AbstractOptions
{
    const POLICY_ALLOW = 'allow';
    const POLICY_DENY  = 'deny';

    /**
     * @var string
     */
    protected $policy = self::POLICY_ALLOW;

    /**
     *
     * @var boolean
     */
    protected $cacheEnabled = false;

    /**
     *
     * @var integer
     */
    protected $defaultTtl = 14400;

    /**
     * @var array
     */
    protected $cacheableActions = [];

    /**
     *
     * @var array
     */
    protected $cacheableRoutes = [];

    /**
     *
     * @var boolean
     */
    protected $debug = false;

    /**
     * varnish cache instances
     *
     * @var array
     */
    protected $servers = [];

    /**
     *
     * @var bool
     */
    protected $useEsi = false;

    /**
     * @var array
     */
    protected $cachingStrategies = [];

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
