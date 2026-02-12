#!/usr/bin/env bash

set -euo pipefail

# Picks the next open EPIC (title starts with EPIC) not marked ready.
gh issue list \
  --state open \
  --limit 100 \
  --json number,title,labels,createdAt \
  --jq '
    map(
      select(.title | test("^EPIC\\b"))
      | select(
          (
            [.labels[].name] | any(. == "status:ready")
          ) | not
        )
    )
    | sort_by(.createdAt)
    | if length == 0 then empty else .[0] end
    | if . == null then empty else "\(.number)\t\(.title)" end
  '
