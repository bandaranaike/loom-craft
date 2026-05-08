# Task System

This directory tracks work in four states:

- `planned`
- `in-progress`
- `completed`
- `archive`

The backlog is now organized by folder and topic:

- `planned/fulfillment`
- `planned/mobile`
- `in-progress/fulfillment`

Use `.ai/tasks/task-board.md` as the human-readable board.

Use `.ai/tasks/_templates/task-template.md` when converting a raw request into a proper task file.

When executing tasks, assume a project-specific skill baseline that includes:

- e-commerce domain reasoning
- order, payment, shipment, and return workflow modeling
- logistics and fulfillment operations awareness
- Laravel backend and React/Inertia implementation judgment

That skill baseline is informed by `.ai/knowledge/skills.md`, but task execution must still be grounded in the codebase and the core knowledge files.
