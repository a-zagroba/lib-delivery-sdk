<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Exception;

class DeliveryGatewayNotFoundException extends BaseException
{
    private const ERR_MSG_TEMPLATE = 'Cannot find delivery gateway with code \'%s\' for order %s.';

    private string $gatewayCode;

    private string $orderNumber;

    public function __construct(string $gatewayCode, string $orderNumber)
    {
        $this->gatewayCode = $gatewayCode;
        $this->orderNumber = $orderNumber;

        $this->message = sprintf(
            self::ERR_MSG_TEMPLATE,
            $this->gatewayCode,
            $this->orderNumber,
        );

        parent::__construct($this->message, self::E_GATEWAY_NOT_FOUND);
    }

    public function getGatewayCode(): string
    {
        return $this->gatewayCode;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }
}
