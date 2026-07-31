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

## Phase 4: AI Order Understanding & Product Matching — **COMPLETED**
- **Goal:** Allow customers to order naturally via WhatsApp using AI intent extraction, deterministic product matching, and local state management.
- **Key Features:** `GeminiAIService` (configurable `gemini-3.5-flash`), `AIOrderParser` with strict Laravel validation, `ProductMatcher` (4-stage deterministic matching), `OrderConversationHandler` (state machine, TTL expiration, price staleness check, local fast-track YES/NO & clarification, and authoritative `OrderService` execution).
- **Test Coverage:** `AIOrderParserTest` (5 tests), `ProductMatcherTest` (4 tests), `OrderConversationHandlerTest` (6 tests)
- **Status:** Complete.

## Phase 5: Advanced Features & Production Readiness — **NOT STARTED**

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
- **Stock Behavior:** Stock is decremented explicitly during `OrderService::createOrder()` inside a secure database transaction.
- **Order Pricing Authority:** Prices provided by any UI or AI are categorically ignored. The server pulls real-time database prices and locks them into `order_items` during creation to ensure historical accuracy. `price_at_display` is strictly a temporary snapshot to detect price changes before confirmation.
- **AI Integration:** AI returns raw structured intent data only (no customer-facing reply text, no prices, no product IDs). All customer-facing messages are built by Laravel. Deterministic responses (YES/NO/clarifications) are fast-tracked locally without AI calls.

## Last completed feature

Phase 4: AI Order Understanding & Product Matching

## Test Results

Tests: 73 passed, 0 failed (198 assertions)

- ExampleTest (Unit): 1 passed
- AuthenticationTest: 3 passed
- BusinessOnboardingTest: 1 passed
- CategoryManagementTest: 12 passed
- ExampleTest (Feature): 1 passed
- OrderManagementTest: 14 passed
- ProductManagementTest: 16 passed
- TenantIsolationTest: 1 passed
- WhatsAppWebhookTest: 5 passed
- WhatsAppMessageServiceTest: 4 passed
- AIOrderParserTest: 5 passed
- ProductMatcherTest: 4 passed
- OrderConversationHandlerTest: 6 passed