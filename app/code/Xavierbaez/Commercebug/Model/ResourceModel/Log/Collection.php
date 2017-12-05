<?php
/**
* Copyright © Pulse Storm LLC 2016
* All rights reserved
*/
namespace Xavierbaez\Commercebug\Model\ResourceModel\Log;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Xavierbaez\Commercebug\Model\Log','Xavierbaez\Commercebug\Model\ResourceModel\Log');
    }
}
