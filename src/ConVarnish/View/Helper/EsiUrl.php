<?php

namespace ConVarnish\View\Helper;

use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Json\Json;
use Zend\View\Helper\AbstractHelper;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class EsiUrl extends AbstractHelper
{
    /**
     *
     * @var LayoutUpdaterInterface
     */
    protected $layoutUpdater;

    /**
     *
     * @param LayoutUpdaterInterface $layoutUpdater
     */
    public function __construct(LayoutUpdaterInterface $layoutUpdater)
    {
        $this->layoutUpdater = $layoutUpdater;
    }

    /**
     *
     * @param string $blockId
     * @return array|string
     */
    public function __invoke($blockId)
    {
        $handles = [];
        foreach ($this->layoutUpdater->getHandles(true) as $handle) {
            $handles[$handle->getName()] = $handle->getPriority();
        }
        $url = $this->getView()->url(
            'esi',
            ['block' => $blockId],
            ['query' => ['handles' => $handles]]
        );
        return $url;
    }
}
