<?php
namespace Wow\CronStatusNotification\Helper;

use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $cronCollection;

    public function __construct(
        CollectionFactory $cronCollection
        ){ 
        $this->cronCollection = $cronCollection;
	}

    public function getCronCollection()
    {
        $currentRunningJob = $this->cronCollection->create()
        ->addFieldToSelect(['schedule_id','job_code','messages'])
        ->addFieldToFilter('status', 'error');

        $paramsData = [
            "data" => $currentRunningJob
        ];

        return $paramsData;
    }

}