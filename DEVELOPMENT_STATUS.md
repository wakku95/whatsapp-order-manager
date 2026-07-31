# Development Status

## Completed

- [x] Project setup
- [x] Authentication
- [x] Business onboarding
- [x] Database (Phase 1 - all 11 tables)
- [x] Multi-tenancy foundation (BelongsToTenant, TenantScope, TenantContext, IdentifyTenant)
- [x] Categories (Phase 2A)
- [x] Products (Phase 2A)
- [ ] Customers
- [ ] Orders
- [ ] WhatsApp webhook
- [ ] WhatsApp sending
- [ ] AI order parsing
- [ ] Dashboard (Phase 1 only - placeholder)
- [ ] Reports
- [ ] Staff
- [ ] Subscription
- [ ] Testing
- [ ] Production deployment

## Current task

Phase 2A (Catalog Management) complete. Products and Categories implemented with full tenant isolation, SKU/slug uniqueness validation, image upload, and safe-delete rules. 
Ready to begin Phase 2B (Order Management) on approval.

## Known issues

None.

## Important decisions

- WhatsApp credentials are stored on a dedicated `whatsapp_connections` table (not on `businesses`).
- Tax is not implemented. Financial fields: subtotal, delivery_fee, discount, total, cod_amount only.
- TenantScope applies automatically to all models using BelongsToTenant trait.
- business_id is NEVER trusted from the client - always resolved from TenantContext.
- IdentifyTenant middleware redirects business-less users to /onboarding on every request.
- Category deletion is blocked if products are attached.
- Product deletion is converted to deactivation if order items exist to preserve historical order data.

## Last completed feature

Phase 2A: Catalog Management (Categories, Products, Images, Prices)

## Phase 2A Test Results

Tests:    28 passed (62 assertions)

- CategoryManagementTest: 12 passed
- ProductManagementTest: 16 passed