<?php

namespace Wow\CmsContentTranslator\Model\DataConverter\Renderer;

use Wow\CmsContentTranslator\Model\DataConverter\AttributesProcessor;
use Wow\CmsContentTranslator\Model\DataConverter\RendererInterface;

/**
 * Class Base
 */
class Products implements RendererInterface
{
    const WIDGET = '{{widget type=';
    const SPACE_SEPARATOR = '" ';
    const EQUAL_SEPARATOR = '=';

    /**
     * @var AttributesProcessor
     */
    private $attributeProcessor;

    /**
     * Slider constructor.
     *
     * @param AttributesProcessor $attributeProcessor
     */
    public function __construct(AttributesProcessor $attributeProcessor)
    {
        $this->attributeProcessor = $attributeProcessor;
    }

    /**
     * @inheritdoc
     */
    public function toArray(\DOMDocument $domDocument, \DOMElement $node): array
    {

        $item = $this->attributeProcessor->getAttributes($node);
        $value = $node->nodeValue;

        if (strpos($value, self::WIDGET) !== false) {
            $value = stripslashes($value);
            $value = $this->explodeWidget($value);
        }else{
            $value = json_decode(stripslashes($value), true);
        }

        if (is_array($value)) {
            $item['conditions'] = $value;
        }

        return $item;
    }

    /**
     * @inheritdoc
     */
    public function processChildren(): bool
    {
        return false;
    }

    public function explodeWidget($string)
    {
        $arrWidget = explode(self::WIDGET, $string);
        $dataWidgetFinal = [];
        foreach ($arrWidget as $keyWidget => $dataWidget) {
            if($dataWidget!=""){
                $widgetNodes = explode(self::SPACE_SEPARATOR, $dataWidget);
                
                foreach ($widgetNodes as $kWN => $vWN) {
                    $arrWidgetNodes = explode(self::EQUAL_SEPARATOR, $vWN);
                    if(count($arrWidgetNodes)==2){

                        if (strpos($arrWidgetNodes[1], '"') !== false) {
                            $arrWidgetNodes[1] = str_replace('"','',$arrWidgetNodes[1]);
                        }
                        $dataWidgetFinal[$arrWidgetNodes[0]] = $arrWidgetNodes[1];
                    }
                    
                }
            }
        }
        // $this->writeLog($dataWidgetFinal);
        return $dataWidgetFinal;
    }

    private function writeLog($message)
    {
        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/reza-test.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        if(is_array($message)){
            $logger->info(print_r($message,true));
        } else {
            $logger->info($message);
        }
    }
}
