<?php 

namespace Wow\QrGenerator\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory = false;
	
    public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	)
	{
		$this->resultPageFactory = $resultPageFactory;
		return parent::__construct($context);
		
	}
	public function execute()
	{
		$resultPage = $this->resultPageFactory->create();

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helperGen = $objectManager->get("\Wow\QrGenerator\Helper\Generator");
		$request = $objectManager->get('\Magento\Framework\App\RequestInterface');
		$params = $request->getParams();
		
		$urltext = "";
		foreach ($params as $key => $value) {
			if($urltext!=""){
				$urltext.="&";
			}
			$urltext.=$key."=".$value;
		}

		// $urlText = $urlBuilder->getUrl()."qrcode/?".$urltext;
		$urlText = "http://google.com/?".$urltext;
		$helperGen->generate($urlText);

		//return $resultPage;
	}
}