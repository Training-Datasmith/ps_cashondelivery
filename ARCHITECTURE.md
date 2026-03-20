# Architecture: ps_cashondelivery

## Purpose

A PrestaShop payment module that adds a "Cash on Delivery" (COD) payment option. When
selected at checkout, the order is placed without online payment processing. A fee can
optionally be added to the order.

## Directory Structure

```
ps_cashondelivery.php   # Main module class and payment provider implementation
views/templates/         # Order confirmation and payment info templates
translations/            # Translation files
tests/                   # PHPStan and unit tests
```

## Key Design Decisions

Implements PrestaShop's `PaymentModule` base class. The `hookPaymentOptions()` method
returns a `PaymentOption` with COD-specific display. No external payment gateway is
involved; the module simply confirms the order and sets the appropriate order status.

## Extension Points

Customize the COD fee logic via module configuration or by overriding the module class.
