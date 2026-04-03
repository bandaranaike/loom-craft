# LoomCraft — Hard Guardrails

- Platform commission is ALWAYS 7%
- Inertia.js only
- No automated refunds
- Vendor approval is mandatory
- Product media handling is mandatory:
  - Images must be uploaded to application storage (local paths stored).
  - Videos must be uploaded to YouTube using the Google API Client for PHP (YouTube URL stored).

Aligned with `.ai/knowledge/core/architecture.md`, `.ai/knowledge/core/best-practices.md`, `.ai/knowledge/core/implementation-guide.md`, and `.ai/knowledge/core/order-process.md`.

## Task File Workflow

- Read `.ai/README.md` and `.ai/tasks/task-board.md` before implementation work starts.
- Convert freeform requests from `.ai/inbox/task-request.md` into normalized task files under `.ai/tasks/planned/`.
- Move task files across `.ai/tasks/planned/`, `.ai/tasks/in-progress/`, and `.ai/tasks/completed/` as work progresses.
- Update relevant `.ai/knowledge/core/*.md` files after completing work.
- Before proceeding with implementation, ask for clarification when requirements are unclear or when extra details are needed.
