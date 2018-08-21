<?php

namespace Sylake\AkeneoProducerBundle\Connector\Listener;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Sylake\AkeneoProducerBundle\Connector\ItemSetInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class AttributeSavedListener
{
    /** @var ItemSetInterface */
    private $itemSet;

    public function __construct(ItemSetInterface $itemSet)
    {
        $this->itemSet = $itemSet;
    }

    public function __invoke(GenericEvent $event)
    {
        $attribute = $event->getSubject();

        if (!$attribute instanceof AttributeInterface) {
            return;
        }

        $this->itemSet->add($attribute);

        /** @var Collection $attributeOptions */
        $attributeOptions = $attribute->getOptions();

        foreach ($attributeOptions as $attributeOption) {
            $this->itemSet->add($attributeOption);
        }
    }
}
