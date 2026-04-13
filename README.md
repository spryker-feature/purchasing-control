# PurchasingControl Module
[![Latest Stable Version](https://poser.pugx.org/spryker-feature/purchasing-control/v/stable.svg)](https://packagist.org/packages/spryker-feature/purchasing-control)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg)](https://php.net/)

The Purchasing Control feature extends Spryker's B2B Company Account and Approval Process with native cost center and budget management. It allows finance teams to define departmental or project-based spending limits, and enforces those limits at checkout alongside Spryker's existing permission-based approval flow.

## Features

- **Cost Center Management** — Create, update, activate, and deactivate cost centers in the Back Office. Link cost centers to business units so assigned buyers are automatically available for selection at checkout.
- **Budget Controls** — Define budgets per cost center with a total amount, period, currency, and enforcement rule (Block, Warn, or Require Approval).
- **Checkout Integration** — Buyers select a cost center and budget during checkout. The system displays the remaining budget and validates the order total before proceeding.
- **Layered Approval Enforcement** — Budget rules run in parallel with Spryker's "Buy up to grand total" permission limits. If either check triggers, the configured action is applied: block the order, warn the buyer, or route it through the approval workflow.
- **Budget Lifecycle** — Budget balance is consumed on order confirmation and restored on cancellation or return.
- **Spend Reporting** — Back Office dashboard showing spend vs. budget per cost center, with filtering by time range, cost center, or buyer, and CSV export.
- **Audit Log** — All cost center, budget, and approval events are recorded with timestamps and actor identity for compliance and finance review.

## Validation Outcomes at Checkout

| Condition | Result |
|---|---|
| Within budget and within permission limit | Buyer completes checkout |
| Exceeds budget — Warn rule | Warning shown; buyer proceeds but approval is required |
| Exceeds budget — Require Approval rule | Quote sent for approval |
| Exceeds Buy-up-to permission limit | Quote sent for approval |
| Exceeds budget — Block rule | Checkout blocked; no approval option |

## Installation

```
composer require spryker-feature/purchasing-control
```

## Documentation

[Spryker Documentation](https://docs.spryker.com)
