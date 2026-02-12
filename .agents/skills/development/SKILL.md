---
name: development
description: "Works GitHub open issues one by one in a loop, sets each issue to In Progress while implementing, and marks it Ready when completed. Use for sequential backlog execution with Laravel Boost best practices."
license: MIT
metadata:
  author: securelens
---

# Development

## When to Apply

Activate this skill when:

- A user asks to process GitHub issues one by one
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
2. Discover the next issue:
   - Run `scripts/next-open-issue.sh` to pick the oldest open issue that is not already `status:in-progress` or `status:ready`.
3. Move issue to In Progress:
   - Run `scripts/issue-status.sh <issue-number> in-progress`.
4. Implement the issue using Laravel best practices:
   - Start with Laravel Boost `application-info`.
   - Use Laravel Boost `search-docs` before code changes for Laravel/Fortify/Livewire/Flux/Pest tasks.
   - Follow existing project conventions and prefer `php artisan make:* --no-interaction`.
   - Add or update Pest tests for every change.
   - Run targeted tests with `php artisan test --compact ...`.
   - Run `vendor/bin/pint --dirty --format agent`.
5. Confirm task completion:
   - Ensure tests pass.
   - Add a concise issue comment summarizing changes and test coverage.
6. Move issue to Ready:
   - Run `scripts/issue-status.sh <issue-number> ready`.
7. Repeat:
   - Return to step 1 and continue until no open issues remain or user stops the loop.

## Implementation Notes

- Never silently skip status updates; report failures and retry once with corrected context.
- Always run preflight commit before the first issue in a loop run.
- Do not close issues automatically unless the user explicitly asks.
- If no issue matches the filter, report backlog is empty for this loop.
- If frontend changes are not visible, remind the user about `npm run build`, `npm run dev`, or `composer run dev`.
