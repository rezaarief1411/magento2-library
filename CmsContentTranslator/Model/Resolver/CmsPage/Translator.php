<?php

declare (strict_types = 1);

namespace Wow\CmsContentTranslator\Model\Resolver\CmsPage;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

/**
 * @inheritdoc
 */
class Translator implements ResolverInterface
{

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $pageHelper = $objectManager->get('\Wow\CmsContentTranslator\Helper\Page');
        $pageData = [];
        try {

            if (isset($value['page_id'])) {
                $content  = $pageHelper->getContentById((int)$value['page_id']);
            } elseif (isset($value['identifier'])) {
                $content = $pageHelper->getContentByIdentifier(
                    (string)$value['identifier'],
                    (int)$context->getExtensionAttributes()->getStore()->getId()
                );
            }
            $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
            $value = $pageHelper->blockConverter($content, $storeId, $context);
            $pageData[] = $value;
        } catch (NoSuchEntityException $e) {
            $pageHelper->writeLog("NoSuchEntityException : ".$e->getMessage());
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $pageData;
    }
   
}
