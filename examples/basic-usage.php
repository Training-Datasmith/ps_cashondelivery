<?php

declare(strict_types=1);

/**
 * Example: Working with the ps_cashondelivery PrestaShop module.
 *
 * ps_cashondelivery adds a "Cash on Delivery" payment option to the checkout.
 * When selected, the order is created without online payment processing.
 * The module hooks into the payment step and order confirmation flow.
 *
 * This file documents common integration patterns.
 */

// --- The module provides a PaymentModuleInterface implementation ---
// PrestaShop automatically renders it on the checkout payment step.
// No direct code needed for standard use.

// --- Hook: paymentOptions ---
// The module registers a PaymentOption for checkout display:
//
// public function hookPaymentOptions(array $params): array
// {
//     $option = new PaymentOption();
//     $option->setModuleName($this->name);
//     $option->setCallToActionText($this->trans('Pay by Cash on Delivery', [], 'Modules.Cashondelivery.Shop'));
//     $option->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true));
//     return [$option];
// }

// --- Hook: actionPaymentConfirmation ---
// Fired after the customer confirms a COD order. Use to send custom notifications:
//
// Hook::register('actionPaymentConfirmation', 'MyModule', 'notifyStoreOwner');
//
// public function notifyStoreOwner(array $params): void
// {
//     $orderId = $params['id_order'];
//     $order   = new Order($orderId);
//     if ($order->module === 'ps_cashondelivery') {
//         // Send SMS or internal notification to staff
//         NotificationService::send("COD order #{$orderId} received.");
//     }
// }

// --- Checking if an order used COD ---
// $order = new Order($orderId);
// $isCod = ($order->module === 'ps_cashondelivery');

// --- Back Office configuration ---
// Modules > Cash on Delivery:
//   - Fee amount (optional surcharge for COD orders)
//   - Tax rule for the fee
//   - Carrier restrictions (available only with certain carriers)
//   - Country restrictions
