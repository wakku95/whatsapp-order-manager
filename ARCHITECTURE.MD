# Architecture

## Application layers

Use this general structure:

HTTP Request
↓
Controller / Livewire Component
↓
Form Request / Validation
↓
Service
↓
Model / Repository when appropriate
↓
Database

For external integrations:

Application
↓
Service Interface
↓
Provider Implementation

Examples:

WhatsAppServiceInterface
AIServiceInterface

## Important services

Create services when appropriate:

- OrderService
- CustomerService
- ProductService
- WhatsAppService
- WhatsAppWebhookService
- AIOrderParserService
- CustomerConversationService
- CODService
- DashboardService
- SubscriptionService
- NotificationService

## WhatsApp architecture

WhatsApp message:

WhatsApp Cloud API
↓
Webhook endpoint
↓
Validate webhook
↓
Store incoming message
↓
Identify business
↓
Identify customer
↓
Conversation service
↓
AI parser when necessary
↓
Order service
↓
Database
↓
Send response through WhatsApp API

## Important

Incoming WhatsApp webhooks should be idempotent.

The same webhook must not create duplicate orders.

Store provider message IDs and use them to prevent duplicate processing.

## Queues

Use queues for operations that do not need to block the HTTP request:

- AI processing
- Sending WhatsApp messages
- Notifications
- Reports
- Background processing

## Security

Every business must only access its own data.

Use policies and authorization.

Never trust business_id from a client request.

Determine business ownership from authenticated context.

## Multi-tenancy

This is a multi-business SaaS.

Every business-owned record must be associated with the correct business/tenant.

Never allow one business to see another business's:

- Customers
- Orders
- Products
- Conversations
- WhatsApp data
- Staff
- Reports

## Database

Use foreign keys.

Use indexes for:

- business_id
- customer_id
- order_id
- phone numbers
- WhatsApp message IDs
- status
- timestamps

Use appropriate unique constraints.