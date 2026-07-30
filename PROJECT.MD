# WhatsApp Order Manager

## Product

We are building a SaaS platform for small businesses that receive customer orders through WhatsApp.

The platform connects a business's WhatsApp Business account to our application.

Customers continue using WhatsApp.

The business owner manages orders from our dashboard.

The application should reduce manual WhatsApp order management.

## Primary target businesses

Initial target:

- Clothing businesses
- Cosmetics businesses
- Bakeries
- Home food businesses
- Small e-commerce businesses
- Gift shops
- Electronics/accessories sellers

## Core concept

Customer sends a WhatsApp message.

Example:

"2 black shirts size L and 1 blue shirt size M"

Our system receives the message through the official WhatsApp Business Cloud API.

The system identifies the customer, understands the order, checks products, calculates prices and creates an order.

The business sees the order inside the dashboard.

## Important

Do NOT build a food delivery marketplace.

This is a WhatsApp order management SaaS.

The initial version is focused on order management.

## Initial payment model

COD only.

Do not implement online customer payments in version 1.

## Main users

1. Business owner
2. Business staff
3. Customer
4. Platform admin

## Main features

Business:

- Register/login
- Business profile
- WhatsApp connection
- Product management
- Categories
- Orders
- Customers
- Order status
- COD tracking
- Dashboard
- Reports
- Staff accounts
- Settings

Customer:

- Customer interacts primarily through WhatsApp
- Customer does not need to install our application

Platform admin:

- Businesses
- Subscriptions
- WhatsApp connections
- Usage
- Support
- System settings

## Technology

Backend:
Laravel 12

Database:
MySQL

Frontend:
Blade + Livewire

CSS:
Tailwind CSS

JavaScript:
Alpine.js where appropriate

Authentication:
Laravel authentication

Queues:
Laravel queues

Cache:
Redis when needed

WhatsApp:
Official WhatsApp Business Cloud API

AI:
AI provider should be abstracted behind a service interface.

Do not tightly couple the entire application to one AI provider.

## Development principle

Build a production-quality MVP.

Do not create fake functionality.

Do not use mock data in production pages.

Do not hardcode business data.

Use migrations, models, relationships, validation, policies and services correctly.

Keep business logic out of Blade views.

Use service classes for complex operations.

Use database transactions where financial/order consistency matters.