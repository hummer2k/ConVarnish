<?php

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish\Listener;

use ConVarnish\Service\VarnishService;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Http\Header\GenericHeader;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Model\ViewModel;

class InjectTagsHeaderListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    public const OPTION_CACHE_TAGS = 'cache_tags';

    /**
     *
     * @var array
     */
    private $cacheTags = [];

    /**
     *
     * @param EventManagerInterface $events
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = -9000)
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_RENDER,
            [$this, 'injectTagsHeader'],
            $priority
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
        $cacheTags = $this->getCacheTags();
        if (!empty($cacheTags)) {
            $headers = $e->getResponse()->getHeaders();
            $tagsHeader = new GenericHeader(
                VarnishService::VARNISH_HEADER_TAGS,
                implode(',', $cacheTags)
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
        $tags = (array) $viewModel->getOption(self::OPTION_CACHE_TAGS, []);
        $this->cacheTags = ArrayUtils::merge($this->cacheTags, $tags);
        if ($viewModel->hasChildren()) {
            foreach ($viewModel->getChildren() as $childViewModel) {
                $this->extractCacheTags($childViewModel);
            }
        }
    }

    /**
     *
     * @return array
     */
    public function getCacheTags()
    {
        return array_filter(array_unique($this->cacheTags));
    }
}
