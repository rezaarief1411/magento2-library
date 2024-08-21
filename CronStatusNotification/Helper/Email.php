<?php
namespace Wow\CronStatusNotification\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $inlineTranslation;
    protected $escaper;
    protected $transportBuilder;
    protected $logger;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $context->getLogger();
    }

    public function sendEmail($paramsData)
    {

        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/cronjobnotif.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $dataCrons = $paramsData["data"]->getData();

        $strCronJobMsg = "";
        foreach ($dataCrons as $dataCron) {
            $strCronJobMsg .= "<tr>";
            $strCronJobMsg .= "<td>".$dataCron["schedule_id"]."</td>";
            $strCronJobMsg .= "<td>".$dataCron["job_code"]."</td>";
            $strCronJobMsg .= "<td>".$dataCron["messages"]."</td>";
            $strCronJobMsg .= "</tr>";
        }

        try {            
            
            $this->inlineTranslation->suspend();

            $recipientEmailsString = $this->getCronStatusEmailRecipient();

            $recipientEmailsArray = explode(",",$recipientEmailsString);


            $sender = [
                'name' => $this->escaper->escapeHtml($this->getStorename()),
                'email' => $this->escaper->escapeHtml($this->getStoreEmail()),
            ];

            $templateVars = [
                'subject' => 'Cron Job Status Notification',
                'data_count' => count($dataCrons),
                'job_msg' => $strCronJobMsg
            ];
            
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('cron_status_template')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars($templateVars)
                ->setFrom($sender)
                ->addTo($recipientEmailsArray)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $logger->info("CronJob Notif Exception Message : ".$e->getMessage());
            $this->logger->debug($e->getMessage());
        }
    }

    public function getStorename()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStoreEmail()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCronStatusEmailRecipient()
    {
        return $this->scopeConfig->getValue(
            'wowcronjobstatus/general/recipient_email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}