<?php
/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish\Strategy;

use Zend\Mvc\MvcEvent;

class RouteStrategy extends AbstractCachingStrategy
{
    /**
     * @inheritDoc
     */
    public function determineTtl(MvcEvent $e)
    {
        $routeMatch      = $e->getRouteMatch();
        $routeName       = $routeMatch->getMatchedRouteName();
        $cacheableRoutes = $this->varnishOptions->getCacheableRoutes();
        $ttl             = $this->getTtlFor($cacheableRoutes, $routeName);

        if (false !== $ttl) {
            $this->ttl = $ttl;
            $e->stopPropagation();
            return $this;
        }
    }
}
