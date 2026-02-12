---
name: development
description: "Works GitHub issues epic-by-epic in a loop, drains all child tasks under one EPIC before moving to the next, sets each task to In Progress while implementing, and marks it Ready when completed."
license: MIT
metadata:
  author: securelens
---

# Development

## When to Apply

Activate this skill when:

- A user asks to process GitHub issues one by one
- A user asks to process work epic-by-epic
- Issue status must move from `Open` to `In Progress` to `Ready`
- Work should continue in a loop until backlog is done or user stops

## Required Tools

- `gh` CLI authenticated for the target repo
- `jq` for JSON filtering
- `git` for preflight commit checkpoints
- Laravel Boost tools for Laravel implementation and validation

## Status Convention

Default status movement uses labels:

- `status:in-progress`
- `status:ready`

If the repo uses GitHub Project V2 Status fields, optional environment variables can also update the Project item status:

- `GH_PROJECT_ID`
- `GH_STATUS_FIELD_ID`
- `GH_STATUS_OPTION_IN_PROGRESS_ID`
- `GH_STATUS_OPTION_READY_ID`

## Workflow Loop

1. Enable auto edits and run repo preflight:
   - Turn on auto edits for this loop run.
   - Run `scripts/preflight-repo.sh "chore: pre-loop checkpoint"` to check for uncommitted changes and commit them before issue work starts.
2. Discover the next EPIC:
   - Run `scripts/next-open-epic.sh` to pick the oldest open epic issue where the title starts with `EPIC`.
3. Drain child tasks in the selected EPIC:
   - Run `scripts/next-open-issue.sh <epic-number>` to pick the next open child task under that epic.
   - Keep selecting and implementing child tasks from the same epic until none remain.
4. Move child task to In Progress:
   - Run `scripts/issue-status.sh <child-issue-number> in-progress`.
5. Implement the child task using Laravel best practices:
   - Start with Laravel Boost `application-info`.
   - Use Laravel Boost `search-docs` before code changes for Laravel/Fortify/Livewire/Flux/Pest tasks.
   - Follow existing project conventions and prefer `php artisan make:* --no-interaction`.
   - Add or update Pest tests for every change.
   - Run targeted tests with `php artisan test --compact ...`.
   - Run `vendor/bin/pint --dirty --format agent`.
6. Confirm child task completion:
   - Ensure tests pass.
   - Add a concise issue comment summarizing changes and test coverage, including `Epic: #<epic-number>`.
7. Move child task to Ready:
   - Run `scripts/issue-status.sh <child-issue-number> ready`.
8. Repeat within the same EPIC:
   - Return to step 3 and continue until `scripts/next-open-issue.sh <epic-number>` returns no result.
9. Close out the EPIC:
   - Add a concise epic comment summarizing all completed child tasks.
   - Move the epic issue to ready with `scripts/issue-status.sh <epic-number> ready`.
10. Move to the next EPIC:
   - Return to step 2 and continue until no open epic remains or the user stops the loop.

## Implementation Notes

- Never silently skip status updates; report failures and retry once with corrected context.
- Always run preflight commit before the first issue in a loop run.
- Epic detection convention: epic issues are GitHub issues where the title starts with `EPIC`.
- Child task resolution order:
  - First use GitHub parent/child links (if available from `gh` in the repository).
  - Fallback: parse referenced issue numbers from the epic body task list.
- Do not process standalone issues outside an epic unless the user asks.
- Do not close issues automatically unless the user explicitly asks.
- If no epic matches the filter, report epic backlog is empty for this loop.
- If frontend changes are not visible, remind the user about `npm run build`, `npm run dev`, or `composer run dev`.
