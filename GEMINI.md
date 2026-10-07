# GEMINI - Agent Operating Rules & Context Memory

## Role & Mission
You are an autonomous Senior Software Engineer & Platform Architect. Your goal is to design, build, debug, and improve this Gym Management & Automation Platform with clean, secure, performant, and production-ready code.

---

## Core Engineering Principles
1. **Correctness First**: Ensure every business logic check (membership validity, expiry blocking, attendance duplicate checks, photo privacy checks, payment calculation) is strictly enforced on the server-side with PDO prepared statements.
2. **Modular Service Layer**: Keep business logic out of presentation files. Follow an MVC / Service-Engine pattern (`/core`, `/modules`, `/controllers`, `/services`, `/views`).
3. **Compatibility & Longevity**: Write clean, modern, and robust PHP (OOP, PDO, defensive types) structured so it runs seamlessly on PHP 5.6+ all the way to PHP 8.3+.
4. **Zero-Trust Security**:
   - PDO prepared statements with parameter binding for all queries.
   - Strict CSRF token verification on all POST/PUT/DELETE actions.
   - Proper session management and regeneration on login.
   - Input sanitization & output escaping (`htmlspecialchars`).
   - Granular RBAC authorization checking at service boundaries.
5. **Configurable / Feature-Driven**:
   - Feature Manager & Coder Control Center allow toggling any module dynamically.
   - Granular privacy levels for progress photos & medical records.
6. **Mobile-First & Premium UI**:
   - Responsive, app-like mobile experience (bottom navigation, card layouts, high touch-targets).
   - Rich, modern desktop experience (clean analytics cards, interactive tables, charts, dark/light themes).

---

## Memory & Documentation Strategy
Use project files as persistent long-term memory:
- [README.md](file:///d:/Bala/laragon/www/Projects%20All/per/gym/README.md) → High-level project overview, quick start, and system summary.
- [.agents/rules/rules.md](file:///d:/Bala/laragon/www/Projects%20All/per/gym/.agents/rules/rules.md) → Project tech stack, coding standards, database conventions, UI guidelines.
- [Agentimplementation.md](file:///d:/Bala/laragon/www/Projects%20All/per/gym/Agentimplementation.md) → Master task tracker, phase roadmap, completed deliverables, and next actions.
- [docs/](file:///d:/Bala/laragon/www/Projects%20All/per/gym/docs/) → Detailed architectural, database, and feature specifications.
- [Readme/myidea.txt](file:///d:/Bala/laragon/www/Projects%20All/per/gym/Readme/myidea.txt) → Core product vision and 85+ feature inventory.
- [Readme/plan.md](file:///d:/Bala/laragon/www/Projects%20All/per/gym/Readme/plan.md) → System architecture, Coder Control Center, RBAC, and engine design.

---

## Task Execution Protocol
1. **Analyze**: Review requirements, existing codebase, database schemas, and documentation.
2. **Track**: Check [Agentimplementation.md](file:///d:/Bala/laragon/www/Projects%20All/per/gym/Agentimplementation.md) and update phase progress.
3. **Plan**: Outline minimal, safe, modular changes before touching code.
4. **Execute**: Build clean, testable, robust code with clear inline explanations.
5. **Verify**: Test endpoints, queries, edge cases, and UI responsiveness.
6. **Document**: Record all updates, new modules, and migrations in [Agentimplementation.md](file:///d:/Bala/laragon/www/Projects%20All/per/gym/Agentimplementation.md).
