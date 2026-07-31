# Development Status

## Completed

- [x] Project setup
- [x] Authentication
- [x] Business onboarding
- [x] Database (Phase 1 - all 11 tables)
- [x] Multi-tenancy foundation (BelongsToTenant, TenantScope, TenantContext, IdentifyTenant)
- [x] Categories (Phase 2A)
- [x] Products (Phase 2A)
- [x] Customers (Core Model for Phase 2B)
- [x] Orders (Phase 2B Server-Side Pricing Engine & OrderService)
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

Phase 2B (Order Service + Server-Side Pricing Engine) is completely backend implemented. `OrderService` encapsulates transactional creation, price locking, and stock deduction perfectly scoped to tenants.
Ready to begin Phase 3 (WhatsApp/AI Integration or Manual Order UI) on approval.

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
- **Stock Behavior:** Stock is decremented explicitly during `OrderService::createOrder()` inside a secure database transaction. Stock restoration for cancelled orders is not implemented in this phase.
- **Order Pricing:** Prices provided by any UI or AI are categorically ignored. The server pulls real-time database prices and locks them into `order_items` during creation to ensure historical accuracy.

## Last completed feature

Phase 2B: Order Service + Server-Side Pricing Engine (Backend Only)

## Test Results

Tests: 49 passed, 0 failed (118 assertions)

- ExampleTest (Unit): 1 passed
- AuthenticationTest: 3 passed
- BusinessOnboardingTest: 1 passed
- CategoryManagementTest: 12 passed
- ExampleTest (Feature): 1 passed
- OrderManagementTest: 14 passed
- ProductManagementTest: 16 passed
- TenantIsolationTest: 1 passed