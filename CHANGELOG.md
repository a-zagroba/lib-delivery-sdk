# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.5.0 - 2026-05-05
- Single-shipment-per-order now uses merchant default parcel size from settings instead of summing item dimensions
- Added `getDefaultParcelWidth/Height/Length/Weight` to `PayseraDeliverySettingsInterface`
- `ShipmentsAdapter::convert()` signature changed: now accepts `PayseraDeliverySettingsInterface` instead of a bool flag

## 0.4.0 - 2026-02-18
- Add credentials validation feature

## 0.3.0 - 2024-06-15
- Sending as one big package

## 0.3.0 - 2024-06-15
- Sending as one big package

## 0.2.1 - 2024-04-29
- Updated documentation

## 0.2.0 - 2024-04-01
- Added support for test mode delivery orders

## 0.1.3 - 2025-01-21
- Updated webtopay/libwebtopay to ^3.1

## 0.1.2 - 2025-01-09
- Added support for prepaid order status

## 0.1.1 - 2024-11-15
- A bunch of small fixes and optimizations

## 0.1.0 - 2024-10-23
- Generated library from RAML
