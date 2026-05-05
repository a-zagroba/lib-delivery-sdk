<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface PayseraDeliverySettingsInterface
{
    public const DELIVERY_GATEWAY_PREFIX = 'paysera_delivery_';
    public const TYPE_COURIER = 'courier';
    public const TYPE_PARCEL_MACHINE = 'parcel-machine';
    public const TYPE_TERMINALS = 'terminals';

    public function getProjectId(): ?int;

    public function getResolvedProjectId(): ?string;

    public function getProjectPassword(): ?string;

    public function isTestModeEnabled(): ?bool;

    public function isHouseNumberFieldEnabled(): ?bool;

    public function getUserAgent(): string;

    public function isSinglePerOrderShipmentEnabled(): bool;

    public function getDefaultParcelWidth(): int;

    public function getDefaultParcelHeight(): int;

    public function getDefaultParcelLength(): int;

    public function getDefaultParcelWeight(): int;
}
