# Antigravity Project Instructions

## Project

This project is a production-quality MVP called
"WhatsApp Order Manager".

Before making changes, read:

- PROJECT.md
- ARCHITECTURE.md
- DATABASE.md
- UI_GUIDELINES.md
- BUSINESS_RULES.md
- DEVELOPMENT_STATUS.md

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

WhatsApp:
Official WhatsApp Business Cloud API

## Rules

1. Do not change the technology stack without asking.

2. Do not rewrite working code unnecessarily.

3. Read existing code before modifying it.

4. Reuse existing components.

5. Follow UI_GUIDELINES.md.

6. Follow BUSINESS_RULES.md.

7. Keep controllers thin.

8. Put complex business logic into services.

9. Validate all external input.

10. Never trust AI-generated prices.

11. Never trust client-side prices.

12. All important financial calculations must happen
    server-side.

13. Maintain business/tenant isolation.

14. Never allow one business to access another
    business's data.

15. WhatsApp webhooks must be idempotent.

16. Do not process the same WhatsApp provider
    message more than once.

17. Use database transactions where required.

18. Do not create fake/mock functionality and
    present it as completed functionality.

19. Do not delete existing functionality unless
    explicitly instructed.

20. Before changing database structure, explain
    migration impact.

21. Write tests for important business logic.

22. After completing a feature, update
    DEVELOPMENT_STATUS.md.

23. If a requirement could affect architecture,
    security or data integrity, ask before coding.

24. Before creating a new UI component, search
    for an existing reusable component.

25. When fixing a bug, find the root cause rather
    than applying an unrelated workaround.

## Development Process

For every feature:

1. Explain what will be built.
2. Inspect relevant existing files.
3. List files that will change.
4. Explain the implementation plan.
5. Implement the feature.
6. Run tests.
7. Fix errors.
8. Explain how I can test it manually.
9. Update DEVELOPMENT_STATUS.md.

Do not implement multiple unrelated features
at the same time.

## UI

Before changing UI:

Read UI_GUIDELINES.md.

Do not introduce random colors,
spacing, typography, buttons or layouts.

Reuse existing components.

## Git

Do not make destructive changes without warning.

Keep changes small and logically grouped.