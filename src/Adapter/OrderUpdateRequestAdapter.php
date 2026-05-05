<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\OrderUpdate;
use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentPointCreate;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Exception\UndefinedDeliveryGatewayException;
use Paysera\DeliverySdk\Util\DeliveryGatewayUtils;

class OrderUpdateRequestAdapter
{
    private ShipmentsAdapter $shipmentsAdapter;
    private OrderNotificationAdapter $notificationCallbackAdapter;
    private ShipmentPointAdapter $shipmentPointAdapter;
    private DeliveryGatewayUtils $gatewayUtils;

    public function __construct(
        ShipmentsAdapter $shipmentsAdapter,
        OrderNotificationAdapter $notificationCallbackAdapter,
        ShipmentPointAdapter $shipmentPointAdapter,
        DeliveryGatewayUtils $gatewayUtils
    ) {
        $this->shipmentsAdapter = $shipmentsAdapter;
        $this->notificationCallbackAdapter = $notificationCallbackAdapter;
        $this->shipmentPointAdapter = $shipmentPointAdapter;
        $this->gatewayUtils = $gatewayUtils;
    }

    public function convert(PayseraDeliveryOrderRequest $request): OrderUpdate
    {
        $orderDto = $request->getOrder();
        $deliveryGateway = $orderDto->getDeliveryGateway();

        if ($deliveryGateway === null) {
            throw new UndefinedDeliveryGatewayException();
        }

        $order = (new OrderUpdate())
            ->setShipmentGatewayCode(
                $this->gatewayUtils->resolveDeliveryGatewayCode($deliveryGateway->getCode())
            )
            ->setShipmentMethodCode(
                $this->gatewayUtils->getShipmentMethodCode($deliveryGateway->getSettings())
            )
            ->setShipments([...$this->shipmentsAdapter->convert($orderDto->getItems(), $request->getDeliverySettings())])
            ->setReceiver(
                $this->shipmentPointAdapter->convert(
                    $orderDto->getShipping(),
                    $request->getDeliverySettings(),
                    ShipmentPointCreate::TYPE_RECEIVER,
                ),
            )
            ->setEshopOrderId($orderDto->getNumber())
        ;

        $projectId = $request->getDeliverySettings()->getResolvedProjectId();

        if ($projectId !== null) {
            $order->setProjectId($projectId);
        }

        if ($orderDto->getNotificationCallback() !== null) {
            $order->setOrderNotification(
                $this->notificationCallbackAdapter->convert($orderDto->getNotificationCallback())
            );
        }

        return $order;
    }
}
