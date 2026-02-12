#!/usr/bin/env bash

set -euo pipefail

commit_message="${1:-chore: pre-loop checkpoint}"

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "Not a git repository." >&2
  exit 1
fi

if [[ -z "$(git status --porcelain)" ]]; then
  echo "Working tree is clean."
  exit 0
fi

git add -A

if git diff --cached --quiet; then
  echo "No staged changes to commit."
  exit 0
fi

git commit -m "$commit_message"
echo "Preflight commit created."

