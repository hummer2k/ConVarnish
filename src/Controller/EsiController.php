<?php

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish\Controller;

use ConLayout\Controller\Plugin\LayoutManager;
use ConLayout\Generator\BlocksGenerator;
use ConLayout\Handle\Handle;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class EsiController extends AbstractActionController
{
    /**
     * return single block for esi processing
     * @codeCoverageIgnore
     * @return ViewModel
     */
    public function blockAction()
    {
        $blockId = $this->params()->fromRoute('block');
        $handles = $this->params()->fromQuery('handles', []);
        /* @var $layoutManager LayoutManager */
        $layoutManager = $this->layoutManager();
        foreach ($handles as $handle => $priority) {
            $layoutManager->addHandle(new Handle($handle, $priority));
        }
        if (!$blockId) {
            return $this->blockNotFound($blockId);
        }
        $layoutManager->generate([BlocksGenerator::NAME => true]);
        if (!$block = $layoutManager->getBlock($blockId)) {
            $block = $this->blockNotFound($blockId);
        }
        $block->setVariable('__ESI__', true);
        $block->setTerminal(true);
        return $block;
    }

    /**
     * @codeCoverageIgnore
     * @param string $blockId
     * @return ViewModel
     */
    protected function blockNotFound($blockId)
    {
        $viewModel = new ViewModel(array(
            'blockId' => $blockId
        ));
        $viewModel->setTemplate('con-varnish/block-not-found');
        return $viewModel;
    }
}
