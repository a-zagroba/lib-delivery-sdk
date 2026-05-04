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

    public function testConvertWithMultipleShipmentsEmptyItems(): void
    {
        $this->settingsMock
            ->method('isSinglePerOrderShipmentEnabled')
            ->willReturn(false);

        $shipments = [...$this->shipmentsAdapter->convert(new OrderItemsCollection([]), $this->settingsMock)];

        $this->assertCount(0, $shipments);
    }

    public function testConvertWithoutSettingsFallsBackToMultipleShipments(): void
    {
        $shipments = [...$this->shipmentsAdapter->convert($this->itemsCollection)];

        $this->assertCount(2, $shipments);
        $this->assertEquals(30, $shipments[0]->getLength());
        $this->assertEquals(20, $shipments[0]->getWidth());
        $this->assertEquals(10, $shipments[0]->getHeight());
        $this->assertEquals(40, $shipments[0]->getWeight());
    }

    public function testConvertWithSingleShipmentUsesDefaultParcelSize(): void
    {
        $this->settingsMock
            ->method('isSinglePerOrderShipmentEnabled')
            ->willReturn(true);
        $this->settingsMock->method('getDefaultParcelLength')->willReturn(25);
        $this->settingsMock->method('getDefaultParcelWidth')->willReturn(15);
        $this->settingsMock->method('getDefaultParcelHeight')->willReturn(10);
        $this->settingsMock->method('getDefaultParcelWeight')->willReturn(5);

        $shipments = [...$this->shipmentsAdapter->convert($this->itemsCollection, $this->settingsMock)];

        $this->assertCount(1, $shipments);
        $shipment = $shipments[0];

        $this->assertEquals(25, $shipment->getLength());
        $this->assertEquals(15, $shipment->getWidth());
        $this->assertEquals(10, $shipment->getHeight());
        $this->assertEquals(5, $shipment->getWeight());
    }

    public function testConvertWithSingleShipmentIgnoresItemDimensions(): void
    {
        $this->settingsMock
            ->method('isSinglePerOrderShipmentEnabled')
            ->willReturn(true);
        $this->settingsMock->method('getDefaultParcelLength')->willReturn(25);
        $this->settingsMock->method('getDefaultParcelWidth')->willReturn(15);
        $this->settingsMock->method('getDefaultParcelHeight')->willReturn(10);
        $this->settingsMock->method('getDefaultParcelWeight')->willReturn(5);

        $itemWithHugeDimensions = $this->createItemMock(9999, 9999, 9999, 9999);
        $items = new OrderItemsCollection([$itemWithHugeDimensions]);

        $shipments = [...$this->shipmentsAdapter->convert($items, $this->settingsMock)];

        $this->assertCount(1, $shipments);
        $this->assertEquals(25, $shipments[0]->getLength());
        $this->assertEquals(15, $shipments[0]->getWidth());
        $this->assertEquals(10, $shipments[0]->getHeight());
        $this->assertEquals(5, $shipments[0]->getWeight());
    }

    public function testConvertWithSingleShipmentEmptyItemsStillUsesDefaults(): void
    {
        $this->settingsMock
            ->method('isSinglePerOrderShipmentEnabled')
            ->willReturn(true);
        $this->settingsMock->method('getDefaultParcelLength')->willReturn(25);
        $this->settingsMock->method('getDefaultParcelWidth')->willReturn(15);
        $this->settingsMock->method('getDefaultParcelHeight')->willReturn(10);
        $this->settingsMock->method('getDefaultParcelWeight')->willReturn(5);

        $shipments = [...$this->shipmentsAdapter->convert(new OrderItemsCollection([]), $this->settingsMock)];

        $this->assertCount(1, $shipments);
        $this->assertEquals(25, $shipments[0]->getLength());
        $this->assertEquals(15, $shipments[0]->getWidth());
        $this->assertEquals(10, $shipments[0]->getHeight());
        $this->assertEquals(5, $shipments[0]->getWeight());
    }

    public function testConvertWithSingleShipmentDoesNotReadItemGetters(): void
    {
        $this->settingsMock
            ->method('isSinglePerOrderShipmentEnabled')
            ->willReturn(true);
        $this->settingsMock->method('getDefaultParcelLength')->willReturn(25);
        $this->settingsMock->method('getDefaultParcelWidth')->willReturn(15);
        $this->settingsMock->method('getDefaultParcelHeight')->willReturn(10);
        $this->settingsMock->method('getDefaultParcelWeight')->willReturn(5);

        $item = $this->createMock(MerchantOrderItemInterface::class);
        $item->expects($this->never())->method('getHeight');
        $item->expects($this->never())->method('getWidth');
        $item->expects($this->never())->method('getLength');
        $item->expects($this->never())->method('getWeight');

        $shipments = [...$this->shipmentsAdapter->convert(new OrderItemsCollection([$item]), $this->settingsMock)];

        $this->assertCount(1, $shipments);
    }

    public function testConvertWithMultipleShipmentsPreservesItemOrder(): void
    {
        $this->settingsMock
            ->method('isSinglePerOrderShipmentEnabled')
            ->willReturn(false);

        $items = new OrderItemsCollection([
            $this->createItemMock(1, 2, 3, 4),
            $this->createItemMock(5, 6, 7, 8),
            $this->createItemMock(9, 10, 11, 12),
        ]);

        $shipments = [...$this->shipmentsAdapter->convert($items, $this->settingsMock)];

        $this->assertCount(3, $shipments);
        $this->assertEquals(3, $shipments[0]->getLength());
        $this->assertEquals(7, $shipments[1]->getLength());
        $this->assertEquals(11, $shipments[2]->getLength());
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
