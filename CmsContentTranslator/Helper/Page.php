<?php
namespace Wow\CmsContentTranslator\Helper;

class Page extends \Magento\Framework\App\Helper\AbstractHelper
{

    const IMG_MEDIA = '{{media url=';
    const MEDIA_QUOTE = '&quot;';
    const WIDGET = '{{widget type=';
    const LT = "&lt;";
    const GT = "&gt;";

    
     /**
     * Get Content by Id
     *
     * @param int $pageId
     * @return string[]
     * @throws GraphQlInputException
     */
    public function getContentById(int $pageId): array
    
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $pageRepository = $objectManager->get('\Magento\Cms\Api\PageRepositoryInterface');
        $page = $pageRepository->getById($pageId);
        $content['content'] = $page->getContent();
        return $content;
    }

    /**
     * Get Content by Identifier
     *
     * @param string $pageIdentifier
     * @param int $storeId
     * @return array
     * @throws GraphQlInputException
     */
    public function getContentByIdentifier(string $pageIdentifier, int $storeId): array
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $pageByIdentifier = $objectManager->get('\Magento\Cms\Api\GetPageByIdentifierInterface');
        $page = $pageByIdentifier->execute($pageIdentifier, $storeId);
        $content['content'] = $page->getContent();
        return $content;
    }

    public function blockConverter($content, $storeId, $context)
    {
        $dataContent = $content['content'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $contentConverterJson = $objectManager->get('\Wow\CmsContentTranslator\Model\PageBuilderConverterToJson');
        $arrConvertedContent = $contentConverterJson->convert($dataContent);
        
        // $this->writeLog($arrConvertedContent);
        
        
        foreach ($arrConvertedContent as $key => $data) {
            $dataItems = $data["items"];
            foreach ($dataItems as $kFinal => $valFinal) {
                
                if(is_array($dataItems[$kFinal])){
                    $valArray = $dataItems[$kFinal];
                    
                    foreach ($valArray as $kValFinal => $vValFinal) {
                        $newKValFinal = str_replace("-","_",$kValFinal);
                        if(is_array($vValFinal)){
                            foreach ($vValFinal as $k => $vV) {
                                $newK = str_replace("-","_",$k);
                                if(is_array($vV)){
                                    foreach ($vV as $kV => $valV) {
                                        $newKv = str_replace("-","_",$kV);
                                        if(is_array($valV)){
                                            foreach ($valV as $iK => $iV) {
                                                $newIk = str_replace("-","_",$iK);
                                                if(is_array($iV)){
                                                    foreach ($iV as $ivK => $ivV) {
                                                        $newIvK = str_replace("-","_",$ivK);
                                                        $arrFinal[$key][$kFinal][$newKValFinal][$newK][$newKv][$newIk][$newIvK] = $this->replaceStringValue($iV[$ivK]);
                                                    }
                                                }else{
                                                    $arrFinal[$key][$kFinal][$newKValFinal][$newK][$newKv][$newIk] = $this->replaceStringValue($valV[$iK]);
                                                }
                                            }
                                        }else{
                                            $arrFinal[$key][$kFinal][$newKValFinal][$newK][$newKv] = $this->replaceStringValue($vV[$kV]);
                                        }
                                    }
                                }else{
                                    $arrFinal[$key][$kFinal][$newKValFinal][$newK] = $this->replaceStringValue($vValFinal[$k]);
                                }
                            }
                        }else{
                            $arrFinal[$key][$kFinal][$newKValFinal] = $this->replaceStringValue($vValFinal);
                        }
                        
                    }
                }else{
                    $newKFinal = str_replace("-","_",$kFinal);
                    $arrFinal[$key][$newKFinal] = $this->replaceStringValue($dataItems[$kFinal]);;
                }

            }
            
        }
        
        $arrResult = [];
        foreach ($arrFinal as $key => $value) {
            $arrResult[]["rows"] = $value;
        }
        // $this->writeLog($arrResult);
        return array(
            "contents"=>$arrResult
        );
    }

    
    public function replaceStringValue($fullString)
    {
        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/reza-test.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $baseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $newString = $fullString;

        if (strpos($newString, self::IMG_MEDIA) !== false) {
            $res = str_replace( self::IMG_MEDIA, $baseUrl, $newString); 
            $resQ = str_replace( '"}}', '', $res); 
            $newString = str_replace( '}}', '', $resQ); 
        }

        if (strpos($newString, self::MEDIA_QUOTE) !== false) {
            $res = str_replace( '{{media url=&quot;', $baseUrl, $newString); 
            $newString = str_replace( '&quot;}}', '', $res);
        }

        if (strpos($newString, self::LT) !== false) {
            $newString = str_replace( self::LT, "<", $newString); 
        }

        if (strpos($newString, self::GT) !== false) {
            $newString = str_replace( self::GT, ">", $newString); 
        }

        if (strpos($newString, "}}") !== false) {
            $newString = str_replace( "}}", "", $newString); 
        }

        $isJson = json_validate($newString);
        if($isJson==true){
            $newString = json_decode($newString,true);
        }

        return $newString;
    }

    public function getStringBetween($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    public function replaceBetween($str_start, $str_end,  $replace, $source_string) 
    {
        $startPos = strpos($source_string, $str_start);
        $endPos = strpos($source_string, $str_end);
        if ($startPos === false || $endPos === false) {
            return $source_string;
        }
        $textToDelete = substr($source_string, $startPos, ($endPos + strlen($str_end)) - $startPos);
     
        return str_replace($textToDelete, $replace, $source_string);
    }

    public function getPrefixTag($data)
    {
        $prefixTag = substr($data, 0, strpos($data, "{{widget"));

        return $prefixTag;
    }

    public function getSuffixTag($data)
    {
        $string = ' ' . $data;
        $ini = strpos($string, '"}}');
        if ($ini == 0) return '';
        $ini += strlen('"}}');
        $suffixTag = substr($data, $ini - 1, strpos($data, '"}}'));

        return $suffixTag;
    }

    public function writeLog($message)
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