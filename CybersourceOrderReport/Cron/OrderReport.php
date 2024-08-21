<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wow\CybersourceOrderReport\Cron;

use Wow\CybersourceOrderReport\Helper\Data as HelperData;

class OrderReport
{
    private $helper;

    /**
     * @param PublisherInterface $publisher
     * @param Data $jsonHelper
     * @param Logger $logger
     * @param ConsumerFactory $consumerFactory
     */
    public function __construct(
        HelperData $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->helper->publish();
    }
}