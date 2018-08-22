<?php

namespace Sylake\AkeneoProducerBundle\Normalization;

use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSet;
use Pim\Component\Connector\Processor\Normalization\ProductProcessor as BaseProductProcessor;
use Webstores\AttributeBundle\Entity\Attribute;

/**
 * Class ProductProcessor
 *
 * @package Sylake\AkeneoProducerBundle\Normalization
 */
final class ProductProcessor extends BaseProductProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $productStandard = parent::process($product);

        $attributeSets = $this->fetchFamilyVariantSetAttributes($product);
        $productStandard['attribute_sets'] = $attributeSets;

        /** @var ProductModelInterface $product */
        $parent = $product->getParent();
        $rootParent = null !== $parent ? $this->fetchRootParentCode($parent) : '';
        $productStandard['root_parent_code'] = $rootParent;

        return $productStandard;
    }

    /**
     * @param EntityWithFamilyVariantInterface $product
     *
     * @return array
     */
    private function fetchFamilyVariantSetAttributes(EntityWithFamilyVariantInterface $product)
    {
        $attributeSets = [];

        $familyVariant = $product->getFamilyVariant();
        if (!$familyVariant) {
            return $attributeSets;
        }

        /** @var VariantAttributeSet[] $variantAttributeSets */
        $variantAttributeSets = $familyVariant->getVariantAttributeSets();

        foreach ($variantAttributeSets as $variantAttributeSet) {
            $attributeCode = $this->getFirstAttributeFromVariantAttributeSet($variantAttributeSet);
            if (empty($attributeCode)) {
                continue;
            }

            $attributeSets[$variantAttributeSet->getLevel()] = $attributeCode;
        }

        return $attributeSets;
    }

    /**
     * @param VariantAttributeSet $variantAttributeSet
     *
     * @return string
     */
    private function getFirstAttributeFromVariantAttributeSet(VariantAttributeSet $variantAttributeSet)
    {
        /** @var Attribute[] $axes */
        $axes = $variantAttributeSet->getAxes();

        // Eerste terugsturen. Er kan maar 1 per level worden geconfigureerd.
        foreach ($axes as $axe) {
            return $axe->getCode();
        }
    }

    /**
     * @param $parent
     *
     * @return mixed
     */
    private function fetchRootParentCode(ProductModelInterface $parent)
    {
        $parentOfParent = $parent->getParent();
        if (null === $parentOfParent) {
            return $parent->getCode();
        }

        return $this->fetchRootParentCode($parentOfParent);
    }
}
