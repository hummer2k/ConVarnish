<?php

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish\Strategy;

use Zend\Mvc\MvcEvent;

class ActionStrategy extends AbstractCachingStrategy
{
    /**
     * @inheritDoc
     */
    public function determineTtl(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        $controller = $routeMatch->getParam('controller');
        $action     = $routeMatch->getParam('action');
        $fullAction = $controller . '::' . $action;

        $cacheableActions = $this->varnishOptions->getCacheableActions();
        $ttl = $this->getTtlFor($cacheableActions, $fullAction);

        if (false !== $ttl) {
            $this->setTtl($ttl);
            $e->stopPropagation();
            return $this;
        }
    }
}
