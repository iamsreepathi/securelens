#!/usr/bin/env bash

set -euo pipefail

epic_number="${1:-}"

if [[ -z "$epic_number" ]]; then
  # Backward-compatible global mode: picks the next open issue
  # not already marked in-progress or ready.
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
  exit 0
fi

# EPIC mode: picks the next open child issue for a specific epic.
# First try GitHub parent-child search. If unavailable/empty, fallback to parsing
# referenced issue numbers in the epic body.
parent_search_output="$(
  gh issue list \
    --state open \
    --search "parent:$epic_number" \
    --limit 100 \
    --json number,title,labels,createdAt \
    --jq '
      map(
        select(
          (
            [.labels[].name] | any(. == "status:ready")
          ) | not
        )
      )
      | sort_by(.createdAt)
      | if length == 0 then empty else .[0] end
      | if . == null then empty else "\(.number)\t\(.title)" end
    ' 2>/dev/null || true
)"

if [[ -n "$parent_search_output" ]]; then
  echo "$parent_search_output"
  exit 0
fi

child_numbers_json="$(
  gh issue view "$epic_number" --json body --jq '.body // ""' \
    | grep -Eo '#[0-9]+' \
    | tr -d '#' \
    | awk '!seen[$0]++' \
    | jq -Rcs '
        split("\n")
        | map(select(length > 0))
        | map(tonumber)
      '
)"

if [[ "$child_numbers_json" == "[]" ]]; then
  exit 0
fi

gh issue list \
  --state open \
  --limit 200 \
  --json number,title,labels,createdAt \
  --jq --argjson childNumbers "$child_numbers_json" '
    map(
      select(.number as $n | $childNumbers | index($n))
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
