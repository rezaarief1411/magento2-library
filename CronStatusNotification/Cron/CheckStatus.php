<?php

namespace Wow\CronStatusNotification\Cron;

use Exception;
use Wow\CronStatusNotification\Helper\Email as HelperEmail;
use Wow\CronStatusNotification\Helper\Data as HelperData;

class CheckStatus
{
    public $emailHelper;
    public $dataHelper;

    public function __construct(
        HelperEmail $emailHelper,
        HelperData $dataHelper
    ){ 
        $this->emailHelper = $emailHelper;
        $this->dataHelper = $dataHelper;
	}

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);

        $paramsData = $this->dataHelper->getCronCollection();
        $this->emailHelper->sendEmail($paramsData);
        
        return $this;
    }
}
