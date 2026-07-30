# Project Instructions

Read these files before making architectural or major implementation changes:

PROJECT.md
ARCHITECTURE.md
DATABASE.md
UI_GUIDELINES.md
BUSINESS_RULES.md
DEVELOPMENT_STATUS.md

## Rules

1. Do not change the technology stack without asking.

2. Do not introduce a new package when existing Laravel functionality can solve the problem.

3. Do not rewrite working code unnecessarily.

4. Follow existing naming conventions.

5. Reuse existing UI components.

6. Follow UI_GUIDELINES.md.

7. Follow BUSINESS_RULES.md.

8. Keep controllers thin.

9. Put complex business logic into services.

10. Validate all external input.

11. Never trust AI-generated structured data without application validation.

12. Never trust client-side prices.

13. Use database transactions where multiple related records must remain consistent.

14. Prevent duplicate WhatsApp webhook processing.

15. Respect tenant/business isolation.

16. Write tests for important business logic.

17. Before changing database structure, explain the migration impact.

18. Do not delete existing functionality unless explicitly requested.

19. Do not create fake/mock functionality and present it as completed functionality.

20. After completing a task, update DEVELOPMENT_STATUS.md.

21. If a requirement is unclear and could affect architecture or data integrity, ask before implementing.

22. Keep UI consistent with UI_GUIDELINES.md.

23. Before creating a new component, search for an existing reusable component.

24. When fixing a bug, identify the root cause instead of applying a superficial workaround.