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
        if ($policy === VarnishOptions::POLICY_ALLOW) {
            $this->setTtl($this->varnishOptions->getDefaultTtl());
        } else {
            $this->setTtl(0);
        }
        $e->stopPropagation();
        return $this;
    }
}
