# UI Guidelines

## Overall style

The application should look like a modern professional SaaS dashboard.

Do not make it look like a generic template.

Design should be:

- Clean
- Modern
- Minimal
- Professional
- Spacious
- Easy for non-technical business owners
- Desktop-first but responsive

## Layout

Desktop:

Left sidebar
+
Top header
+
Main content

Sidebar should contain:

Dashboard
Orders
Customers
Products
WhatsApp
Reports
Staff
Settings

The active menu item must be visually obvious.

## Colors

Use a consistent design system.

Primary:
#16A34A

Primary dark:
#15803D

Background:
#F8FAFC

Card:
#FFFFFF

Text:
#0F172A

Muted text:
#64748B

Border:
#E2E8F0

Success:
#16A34A

Warning:
#F59E0B

Danger:
#DC2626

Info:
#2563EB

Do not introduce random colors.

## Typography

Use a clean sans-serif font.

Headings:
strong but not oversized.

Body:
easy to read.

Use consistent font sizes.

## Cards

Cards should have:

- White background
- Subtle border
- Moderate border radius
- Minimal shadow
- Consistent padding

Do not use excessive shadows.

## Buttons

Primary buttons:
solid primary color.

Secondary:
white/light background with border.

Danger:
red.

Buttons should have consistent height, padding and border radius.

## Forms

Labels above inputs.

Inputs should have:

- consistent height
- border
- focus state
- error state
- clear placeholder

Validation errors should appear close to the field.

## Tables

Tables should be:

- clean
- readable
- horizontally scrollable on small screens
- consistent row height

Use status badges.

## Status badges

Order statuses must have consistent visual treatment.

Pending:
warning

Confirmed:
info

Preparing:
info

Ready:
success

Completed:
success

Cancelled:
danger

## Empty states

Every major list should have a useful empty state.

Example:

No orders yet.

Your WhatsApp orders will appear here.

## Loading

Use skeletons/spinners where appropriate.

Don't leave the user wondering whether something is loading.

## Responsive

The dashboard must work on:

Desktop
Laptop
Tablet
Mobile

Do not create separate completely different designs unless necessary.

## Important rule

Before creating a new UI component, check whether an existing component can be reused.

Create reusable components for:

- Button
- Input
- Select
- Modal
- Badge
- Card
- Table
- Alert
- EmptyState
- PageHeader
- Sidebar
- Topbar