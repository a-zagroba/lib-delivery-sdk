<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Service;

use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliverySdk\Client\DeliveryApiClient;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Exception\DeliveryOrderRequestException;
use Paysera\DeliverySdk\Repository\MerchantOrderRepositoryInterface;

class DeliveryOrderService
{
    private const LOG_MESSAGE_STARTED = 'Attempting to perform operation \'%s\' of delivery order for order id %s with project id: %s';
    private const LOG_MESSAGE_COMPLETED = 'Operation \'%s\' of delivery order %s for order id %d is completed.';

    private MerchantOrderRepositoryInterface $merchantOrderRepository;
    private DeliveryApiClient $deliveryApiClient;
    private DeliveryLoggerInterface $logger;

    public function __construct(
        MerchantOrderRepositoryInterface $merchantOrderRepository,
        DeliveryApiClient $deliveryApiClient,
        DeliveryLoggerInterface $logger
    ) {
        $this->deliveryApiClient = $deliveryApiClient;
        $this->logger = $logger;
        $this->merchantOrderRepository = $merchantOrderRepository;
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return MerchantOrderInterface|null
     * @throws DeliveryOrderRequestException
     */
    public function createDeliveryOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): ?MerchantOrderInterface
    {
        $this->logStepStarted(DeliveryApiClient::ACTION_CREATE, $deliveryOrderRequest);
        $deliveryOrder = $this->handleCreating($deliveryOrderRequest);
        $this->logStepCompleted(DeliveryApiClient::ACTION_CREATE, $deliveryOrderRequest, $deliveryOrder);

        return $deliveryOrderRequest->getOrder();
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return MerchantOrderInterface|null
     * @throws DeliveryOrderRequestException
     */
    public function updateDeliveryOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): ?MerchantOrderInterface
    {
        $this->logStepStarted(DeliveryApiClient::ACTION_UPDATE, $deliveryOrderRequest);
        $deliveryOrder = $this->deliveryApiClient->patchOrder($deliveryOrderRequest);
        $this->logStepCompleted(DeliveryApiClient::ACTION_UPDATE, $deliveryOrderRequest, $deliveryOrder);

        return $deliveryOrderRequest->getOrder();
    }

    /**
     * @throws DeliveryOrderRequestException
     */
    public function prepaidDeliveryOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): ?MerchantOrderInterface
    {
        $this->logStepStarted(DeliveryApiClient::ACTION_PREPAID, $deliveryOrderRequest);
        $deliveryOrder = $this->deliveryApiClient->prepaidOrder($deliveryOrderRequest);
        $this->logStepCompleted(DeliveryApiClient::ACTION_PREPAID, $deliveryOrderRequest, $deliveryOrder);
        return $deliveryOrderRequest->getOrder();
    }

    #region Handling

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     */
    private function handleCreating(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        $order = $deliveryOrderRequest->getOrder();
        $deliveryOrder = $this->deliveryApiClient->postOrder($deliveryOrderRequest);

        $order->setDeliveryOrderId($deliveryOrder->getId());
        $order->setDeliveryOrderNumber($deliveryOrder->getNumber());
        $this->merchantOrderRepository->save($order);

        return $deliveryOrder;
    }

    #endregion

    #region Service Methods

    private function logStepStarted(string $action, PayseraDeliveryOrderRequest $request): void
    {
        $this->logger->info(
            sprintf(
                self::LOG_MESSAGE_STARTED,
                $action,
                $request->getOrder()->getNumber(),
                $request->getDeliverySettings()->getProjectId()
            )
        );
    }

    private function logStepCompleted(
        string $action,
        PayseraDeliveryOrderRequest $deliveryOrderRequest,
        Order $deliveryOrder
    ): void {
        $order = $deliveryOrderRequest->getOrder();
        $orderNumber = $deliveryOrder->getNumber();

        $this->logger->info(
            sprintf(
                self::LOG_MESSAGE_COMPLETED,
                $action,
                $orderNumber,
                $order->getNumber(),
            )
        );
    }

    #endregion
}
