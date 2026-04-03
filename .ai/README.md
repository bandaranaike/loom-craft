# AI Knowledge Center

This directory is the project knowledge center and task operations area.

## Working Rule

For every task:

1. Read this file first.
2. Read the relevant documents under `.ai/knowledge/core/`.
3. Check `.ai/tasks/task-board.md` before starting work.
4. If the request starts as freeform input, convert it into a proper task file from `.ai/inbox/task-request.md`.
5. After finishing the task, update the relevant knowledge documents and the task board.

## Directory Layout

- `.ai/inbox/`
  - User freeform task input area.
- `.ai/knowledge/core/`
  - Source-of-truth project knowledge.
- `.ai/knowledge/assets/`
  - Supporting prompts, generated data, brand assets, and source product images.
- `.ai/tasks/planned/`
  - Approved or captured tasks not started yet.
- `.ai/tasks/in-progress/`
  - Tasks currently being implemented.
- `.ai/tasks/completed/`
  - Finished tasks with implementation notes.
- `.ai/tasks/archive/`
  - Legacy files kept for historical reference.

## Default Reading Order

Start here, then read only what is relevant:

1. `.ai/knowledge/core/guardrails.md`
2. `.ai/knowledge/core/best-practices.md`
3. `.ai/knowledge/core/implementation-guide.md`
4. `.ai/knowledge/core/implementation-status.md`
5. Domain-specific files as needed:
   - `.ai/knowledge/core/architecture.md`
   - `.ai/knowledge/core/db-schema.md`
   - `.ai/knowledge/core/order-process.md`
   - `.ai/knowledge/core/deployment.md`

## Task Workflow

1. User writes freely in `.ai/inbox/task-request.md`.
2. Convert that request into a normalized task file using `.ai/tasks/_templates/task-template.md`.
3. Place the new task in `.ai/tasks/planned/`.
4. Move it to `.ai/tasks/in-progress/` when implementation starts.
5. Move it to `.ai/tasks/completed/` when work is done and verified.
6. Update `.ai/tasks/task-board.md`.
7. Update any affected knowledge files in `.ai/knowledge/core/`.
