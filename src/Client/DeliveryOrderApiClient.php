<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Client;

use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliverySdk\Adapter\DeliveryOrderRequestAdapterFacade;
use Paysera\DeliverySdk\Client\Provider\MerchantClientProvider;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Exception\MerchantClientNotFoundException;
use Paysera\DeliverySdk\Exception\UndefinedDeliveryOrderException;

class DeliveryOrderApiClient
{
    private DeliveryOrderRequestAdapterFacade $orderRequestAdapter;
    private MerchantClientProvider $merchantClientProvider;

    public function __construct(
        DeliveryOrderRequestAdapterFacade $orderRequestAdapter,
        MerchantClientProvider $merchantClientProvider
    ) {
        $this->orderRequestAdapter = $orderRequestAdapter;
        $this->merchantClientProvider = $merchantClientProvider;
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws MerchantClientNotFoundException
     */
    public function create(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this
            ->merchantClientProvider
            ->getMerchantClient($deliveryOrderRequest->getDeliverySettings())
            ->createOrder(
                $this->orderRequestAdapter->convertCreate($deliveryOrderRequest)
            )
        ;
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws UndefinedDeliveryOrderException
     * @throws MerchantClientNotFoundException
     */
    public function update(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        $deliveryOrderId = $deliveryOrderRequest->getOrder()->getDeliveryOrderId();

        if ($deliveryOrderId === null) {
            throw new UndefinedDeliveryOrderException($deliveryOrderRequest->getOrder());
        }

        return $this
            ->merchantClientProvider
            ->getMerchantClient($deliveryOrderRequest->getDeliverySettings())
            ->updateOrder(
                $deliveryOrderId,
                $this->orderRequestAdapter->convertUpdate($deliveryOrderRequest)
            )
        ;
    }

    public function get(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        $deliveryOrderId = $deliveryOrderRequest->getOrder()->getDeliveryOrderId();

        if ($deliveryOrderId === null) {
            throw new UndefinedDeliveryOrderException($deliveryOrderRequest->getOrder());
        }

        return $this
            ->merchantClientProvider
            ->getMerchantClient($deliveryOrderRequest->getDeliverySettings())
            ->getOrder(
                $deliveryOrderRequest->getOrder()->getDeliveryOrderId()
            )
        ;
    }
}
