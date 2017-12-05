<?php
/**
* Copyright © Pulse Storm LLC 2016
* All rights reserved
*/
namespace Xavierbaez\Commercebug\Controller\Adminhtml\Pulsestorm\Commercebug;

class Viewlog extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;        
        return parent::__construct($context);
    }
    
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
//         $resultPage->setActiveMenu('Xavierbaez_Commercebug::commerce_bug_logs');
//         $resultPage->getConfig()->getTitle()->prepend(__('Commerce Bug Logs'));
        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Xavierbaez_Commercebug::admin_log_menu');
    }    
}
