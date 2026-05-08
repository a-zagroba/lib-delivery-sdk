<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\phpunit\Adapter;

use Paysera\DeliverySdk\Adapter\ShipmentsAdapter;
use Paysera\DeliverySdk\Collection\OrderItemsCollection;
use Paysera\DeliverySdk\Entity\MerchantOrderItemInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;
use PHPUnit\Framework\TestCase;

class ShipmentsAdapterTest extends TestCase
{
    private ShipmentsAdapter $shipmentsAdapter;
    private PayseraDeliverySettingsInterface $settingsMock;
    private OrderItemsCollection $itemsCollection;

    protected function setUp(): void
    {
        $this->shipmentsAdapter = new ShipmentsAdapter();
        $this->settingsMock = $this->createMock(PayseraDeliverySettingsInterface::class);

        $item1 = $this->createItemMock(10, 20, 30, 40);
        $item2 = $this->createItemMock(50, 60, 70, 80);

        $this->itemsCollection = new OrderItemsCollection([$item1, $item2]);
    }

    public function testConvertWithMultipleShipments(): void
    {
        $this->settingsMock
            ->method('isSinglePerOrderShipmentEnabled')
            ->willReturn(false);

        $shipments = [...$this->shipmentsAdapter->convert($this->itemsCollection, $this->settingsMock)];

        $this->assertCount(2, $shipments);

        $expectedValues = [
            [30, 20, 10, 40],
            [70, 60, 50, 80],
        ];

        foreach ($shipments as $i => $shipment) {
            [$length, $width, $height, $weight] = $expectedValues[$i];
            $this->assertEquals($length, $shipment->getLength());
            $this->assertEquals($width, $shipment->getWidth());
            $this->assertEquals($height, $shipment->getHeight());
            $this->assertEquals($weight, $shipment->getWeight());
        }
    }

    public function testConvertWithSingleShipment(): void
    {
        $this->settingsMock
            ->method('isSinglePerOrderShipmentEnabled')
            ->willReturn(true);
        $this->settingsMock->method('getDefaultParcelLength')->willReturn(100);
        $this->settingsMock->method('getDefaultParcelWidth')->willReturn(80);
        $this->settingsMock->method('getDefaultParcelHeight')->willReturn(60);
        $this->settingsMock->method('getDefaultParcelWeight')->willReturn(120);

        $shipments = [...$this->shipmentsAdapter->convert($this->itemsCollection, $this->settingsMock)];

        $this->assertCount(1, $shipments);
        $shipment = $shipments[0];

        $this->assertEquals(100, $shipment->getLength());
        $this->assertEquals(80, $shipment->getWidth());
        $this->assertEquals(60, $shipment->getHeight());
        $this->assertEquals(120, $shipment->getWeight());
    }

    public function testConvertWithSingleShipmentReturnsNothingWhenItemsAreEmpty(): void
    {
        $this->settingsMock
            ->method('isSinglePerOrderShipmentEnabled')
            ->willReturn(true);

        $shipments = [...$this->shipmentsAdapter->convert(new OrderItemsCollection([]), $this->settingsMock)];

        $this->assertCount(0, $shipments);
    }

    private function createItemMock(int $height, int $width, int $length, int $weight): MerchantOrderItemInterface
    {
        $mock = $this->createMock(MerchantOrderItemInterface::class);
        $mock->method('getHeight')->willReturn($height);
        $mock->method('getWidth')->willReturn($width);
        $mock->method('getLength')->willReturn($length);
        $mock->method('getWeight')->willReturn($weight);
        return $mock;
    }
}
