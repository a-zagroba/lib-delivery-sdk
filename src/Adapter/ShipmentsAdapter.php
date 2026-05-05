<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentCreate;
use Paysera\DeliverySdk\Collection\OrderItemsCollection;
use Paysera\DeliverySdk\Entity\MerchantOrderItemInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;

class ShipmentsAdapter
{
    /**
     * @param OrderItemsCollection<MerchantOrderItemInterface> $items
     * @return iterable<ShipmentCreate>
     */
    public function convert(
        OrderItemsCollection $items,
        PayseraDeliverySettingsInterface $deliverySettings
    ): iterable {
        if ($deliverySettings->isSinglePerOrderShipmentEnabled()) {
            return $this->createSingleShipment($deliverySettings);
        }
        return $this->createMultipleShipments($items);
    }

    /**
     * @return iterable<ShipmentCreate>
     */
    private function createSingleShipment(PayseraDeliverySettingsInterface $deliverySettings): iterable
    {
        return [
            (new ShipmentCreate())
                ->setLength($deliverySettings->getDefaultParcelLength())
                ->setWidth($deliverySettings->getDefaultParcelWidth())
                ->setHeight($deliverySettings->getDefaultParcelHeight())
                ->setWeight($deliverySettings->getDefaultParcelWeight()),
        ];
    }

    /**
     * @param OrderItemsCollection<MerchantOrderItemInterface> $items
     * @return iterable<ShipmentCreate>
     */
    private function createMultipleShipments(OrderItemsCollection $items): iterable
    {
        foreach ($items as $item) {
            yield (new ShipmentCreate())
                ->setHeight($item->getHeight())
                ->setWidth($item->getWidth())
                ->setLength($item->getLength())
                ->setWeight($item->getWeight())
            ;
        }
    }
}
