<?php
namespace ConVarnish\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class VarnishOptions
    extends AbstractOptions
{
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
     *
     * @var array
     */
    protected $cacheableRoutes = array();
    
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
    protected $servers = array();
    
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
    public function setCacheableRoutes($cacheableRoutes)
    {
        $this->cacheableRoutes = $cacheableRoutes;
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
    
    public function getDebug()
    {
        return $this->debug;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }
    
    public function getServers()
    {
        return $this->servers;
    }

    public function setServers($servers)
    {
        $this->servers = $servers;
        return $this;
    }


}
