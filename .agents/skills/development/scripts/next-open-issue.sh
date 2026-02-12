#!/usr/bin/env bash

set -euo pipefail

# Picks the next open issue not already marked in-progress or ready.
gh issue list \
  --state open \
  --limit 50 \
  --json number,title,labels,createdAt \
  --jq '
    map(
      select(
        (
          [.labels[].name] | any(. == "status:in-progress" or . == "status:ready")
        ) | not
      )
    )
    | sort_by(.createdAt)
    | if length == 0 then empty else .[0] end
    | if . == null then empty else "\(.number)\t\(.title)" end
  '

