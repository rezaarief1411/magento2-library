<?php
namespace Wow\CronStatusNotification\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Magento\Framework\App\State;
use Wow\CronStatusNotification\Helper\Email as HelperEmail;
use Wow\CronStatusNotification\Helper\Data as HelperData;

/**
 * Class CheckStatus
 */
class CheckStatus extends Command
{

    public $state;
    public $emailHelper;
    public $dataHelper;

    public function __construct(
        State $state, 
        HelperEmail $emailHelper,
        HelperData $dataHelper,
        $name = null)
    { 
        $this->state = $state;
        $this->emailHelper = $emailHelper;
        $this->dataHelper = $dataHelper;
        parent::__construct($name);
	}

    protected function configure()
    {
        $this->setName('wow:cronstatus:check');
        $this->setDescription('Check Cron Status');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        $paramsData = $this->dataHelper->getCronCollection();
        $this->emailHelper->sendEmail($paramsData);

        return 1;
    }

}