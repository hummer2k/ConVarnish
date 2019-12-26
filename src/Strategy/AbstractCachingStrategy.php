<?php

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish\Strategy;

use ConVarnish\Listener\InjectCacheHeaderListener;
use ConVarnish\Options\VarnishOptions;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;

abstract class AbstractCachingStrategy implements CachingStrategyInterface
{
    use ListenerAggregateTrait;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @var VarnishOptions
     */
    protected $varnishOptions;

    /**
     * AbstractCachingStrategy constructor.
     * @param VarnishOptions $varnishOptions
     */
    public function __construct(VarnishOptions $varnishOptions)
    {
        $this->varnishOptions = $varnishOptions;
    }

    /**
     * @inheritDoc
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->getSharedManager()->attach(
            InjectCacheHeaderListener::class,
            InjectCacheHeaderListener::EVENT_DETERMINE_TTL,
            [$this, 'determineTtl'],
            $priority
        );
    }

    /**
     * @param array $config
     * @param $string
     * @return bool|int
     */
    protected function getTtlFor(array $config, $string)
    {
        if (isset($config[$string])) {
            return (int) $config[$string];
        }
        foreach ($config as $pattern => $ttl) {
            if (fnmatch($pattern, $string, FNM_NOESCAPE)) {
                return (int) $ttl;
            }
        }
        return false;
    }

    /**
     * @param MvcEvent $e
     * @return mixed
     */
    abstract public function determineTtl(MvcEvent $e);

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @param $ttl
     */
    protected function setTtl($ttl)
    {
        $this->ttl = (int) $ttl;
    }
}
