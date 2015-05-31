<?php
namespace ConVarnish\Controller;

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
     *
     * @return ViewModel
     */
    public function blockAction()
    {
        $blockId = $this->params()->fromRoute('block');
        if (!$blockId) {
            return $this->blockNotFound($blockId);
        }
        $this->layoutManager()->load();
        if (!$block = $this->layoutManager()->getBlock($blockId)) {
            $block = $this->blockNotFound($blockId);
        }
        $block->setVariable('__ESI__', true);
        $block->setTerminal(true);
        return $block;
    }

    /**
     *
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
