<?php
namespace ConVarnish\Controller;

use ConLayout\Controller\Plugin\LayoutManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
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
            $layoutManager->addHandle($handle, $priority);
        }
        if (!$blockId) {
            return $this->blockNotFound($blockId);
        }
        $layoutManager->load();
        if (!$block = $layoutManager->getBlock($blockId)) {
            $block = $this->blockNotFound($blockId);
        }
        $block->setVariable('__ESI__', true);
        $block->setTerminal(true);
        return $block;
    }

    /**
     * @codeCoverageIgnore
     * @param string $blockName
     * @return ViewModel
     */
    protected function blockNotFound($blockName)
    {
        $viewModel = new ViewModel(array(
            'blockName' => $blockName
        ));
        $viewModel->setTemplate('con-varnish/block-not-found');
        return $viewModel;
    }
}
