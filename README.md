# Buddhist Bihar Donation Management System

Laravel 12 foundation for one Buddhist Bihar in Bangladesh. The public donation workflow is deliberately verification-first: submit payment information, stay pending, and receive a final receipt only after an administrator confirms the donation.

## Current foundation

- Donation, donor, purpose, bank account, payment setting, content, banner, event, and audit-log schema
- Secure private payment-screenshot storage on Laravel's `local` disk
- Public APIs for active payment settings, purposes, donation submission, and receipt-plus-mobile status checks
- Conditional validation for bKash, Nagad, and bank donations, including Bangladesh mobile normalization and duplicate provider TXID protection
- Seeded placeholder data only — no real payment details are stored in source control

## Local setup

Use PHP 8.2+ with `zip`, `openssl`, `mbstring`, `pdo_mysql`, and `fileinfo` enabled. Then run:

```powershell
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

Configure MySQL credentials in `.env` before running migrations. Change the seeded administrator password immediately after authentication is added.

## Next implementation slices

1. Vue public landing page and Bengali donation/status/receipt views.
2. Session-authenticated admin dashboard, review/confirm/reject workflow, and private screenshot endpoint.
3. Banner/event/content/settings CRUD, reports/exports, PDF receipts, automated tests, and cPanel deployment guide.
