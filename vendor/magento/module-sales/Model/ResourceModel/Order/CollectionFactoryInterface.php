<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order;

/**
 * Class CollectionFactoryInterface
 */
interface CollectionFactoryInterface
{
    /**
     * Create class instance with specified parameters
     *
     * @param int $customerId
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function create($customerId = null);
}