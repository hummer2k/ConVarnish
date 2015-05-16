<?php

namespace ConVarnish\Listener;

use ConVarnish\Service\VarnishService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Http\Header\GenericHeader;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class InjectTagsHeaderListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     *
     * @var array
     */
    private $cacheTags = [];

    /**
     *
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_RENDER,
            [$this, 'injectTagsHeader'],
            -9000
        );
    }

    /**
     *
     * @param MvcEvent $e
     */
    public function injectTagsHeader(MvcEvent $e)
    {
        /* @var $layout ViewModel */
        $layout = $e->getViewModel();
        $this->extractCacheTags($layout);
        $cacheTags = array_filter(array_unique($this->cacheTags));
        if (!empty($cacheTags)) {
            $headers = $e->getResponse()->getHeaders();
            $tagsHeader = new GenericHeader(
                VarnishService::VARNISH_HEADER_TAGS,
                implode(',', $this->cacheTags)
            );
            $headers->addHeader($tagsHeader);
        }
    }

    /**
     *
     * @param ViewModel $viewModel
     */
    private function extractCacheTags(ViewModel $viewModel)
    {
        if ($viewModel->getOption('esi') && !$viewModel->terminate()) {
            return;
        }
        $tags = (array) $viewModel->getOption('cache_tags', []);
        $this->cacheTags = ArrayUtils::merge($this->cacheTags, $tags);
        if ($viewModel->hasChildren()) {
            foreach ($viewModel->getChildren() as $childViewModel) {
                $this->extractCacheTags($childViewModel);
            }
        }
    }
}
