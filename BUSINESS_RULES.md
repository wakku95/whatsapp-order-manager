# Business Rules

## Business

A business can have:

- One primary owner
- Multiple staff users
- Multiple products
- Multiple categories
- Multiple customers
- Multiple orders
- One or more WhatsApp numbers depending on subscription

## Customer

Customers are identified primarily by WhatsApp phone number within a business.

The same WhatsApp number should not create duplicate customer records for the same business.

## Product

Product fields:

- Name
- Description
- Price
- SKU
- Category
- Stock
- Is active
- Image

Products can be unavailable.

Unavailable products must not be automatically added to confirmed orders.

## Order

Initial order statuses:

pending
confirmed
preparing
ready
out_for_delivery
delivered
cancelled

Order status transitions must be controlled.

Do not allow arbitrary status changes.

## COD

Version 1 uses Cash on Delivery.

The system records:

- subtotal
- delivery fee
- discount
- total
- COD amount
- payment status

Possible COD payment statuses:

pending
collected
failed
refunded

## WhatsApp

Every incoming WhatsApp message must be stored.

Store provider message ID.

Never process the same provider message twice.

## AI

AI may:

- understand natural language
- identify products
- identify quantities
- ask clarification questions
- generate customer responses

AI must NOT directly change important financial/order state without validation.

AI output must be converted into structured data and validated by application code.

Example:

AI says:

"2 black shirts"

Application verifies:

- Product exists
- Product is active
- Quantity is valid
- Price comes from database

Never trust AI for price.

## Order totals

Prices must always come from the database.

Never accept prices calculated by the client or AI.

Calculate order totals on the server.

## Customer communication

Important order events should generate appropriate WhatsApp messages.

Do not spam customers.

## Audit

Important order changes should be logged.