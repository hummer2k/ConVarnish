<?php
namespace ConVarnish\Controller;

use Zend\Mvc\Controller\AbstractActionController,
    Zend\View\Model\ViewModel;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class EsiController
    extends AbstractActionController
{
    /**
     * return single block for esi processing
     * 
     * @return ViewModel
     */
    public function blockAction()
    {
        $blockName = $this->params()->fromRoute('block');
        $handles   = (array) $this->params()->fromQuery('handles');
        if (!$blockName) {
            return $this->blockNotFound($blockName);
        }
       
        /* @var $blockManager \ConLayout\Controller\Plugin\BlockManager */
        $blockManager = $this->blockManager();         
        $blockManager->setHandles($handles);
        /* @var $block ViewModel */
        $block = $blockManager->getBlock($blockName);
                
        if (!$block instanceof ViewModel) {
            return $this->blockNotFound($blockName);
        }
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
        $viewModel->setTemplate('block-not-found');
        return $viewModel;
    }
}
