<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish\Strategy;

use ConVarnish\Options\VarnishOptions;
use Zend\Mvc\MvcEvent;

class DefaultStrategy extends AbstractCachingStrategy
{
    /**
     * @inheritDoc
     */
    public function determineTtl(MvcEvent $e)
    {
        $policy = $this->varnishOptions->getPolicy();
        $this->ttl = $policy === VarnishOptions::POLICY_ALLOW
            ? $this->varnishOptions->getDefaultTtl()
            : 0;
        $e->stopPropagation();
        return $this;
    }
}
