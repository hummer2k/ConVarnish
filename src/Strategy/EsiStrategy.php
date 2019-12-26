<?php

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish\Strategy;

use Laminas\Mvc\MvcEvent;

class EsiStrategy extends AbstractCachingStrategy
{
    /**
     * @inheritDoc
     */
    public function determineTtl(MvcEvent $e)
    {
        $viewModel = $e->getViewModel();
        $esiOptions = (array) $viewModel->getOption('esi', []);
        if (isset($esiOptions['ttl'])) {
            $this->setTtl($esiOptions['ttl']);
            $e->stopPropagation();
            return $this;
        }
    }
}
