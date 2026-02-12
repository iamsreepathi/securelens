#!/usr/bin/env bash

set -euo pipefail

if [[ $# -ne 2 ]]; then
  echo "Usage: $0 <issue-number> <in-progress|ready>" >&2
  exit 1
fi

issue_number="$1"
target_status="$2"

case "$target_status" in
  in-progress)
    add_label="status:in-progress"
    remove_label="status:ready"
    project_option_id="${GH_STATUS_OPTION_IN_PROGRESS_ID:-}"
    ;;
  ready)
    add_label="status:ready"
    remove_label="status:in-progress"
    project_option_id="${GH_STATUS_OPTION_READY_ID:-}"
    ;;
  *)
    echo "Invalid status: $target_status" >&2
    echo "Allowed values: in-progress, ready" >&2
    exit 1
    ;;
esac

# Keep label status canonical.
gh issue edit "$issue_number" --remove-label "$remove_label" >/dev/null 2>&1 || true
gh issue edit "$issue_number" --add-label "$add_label" >/dev/null

# Optional: sync GitHub Project V2 status if all required variables are set.
project_id="${GH_PROJECT_ID:-}"
status_field_id="${GH_STATUS_FIELD_ID:-}"

if [[ -n "$project_id" && -n "$status_field_id" && -n "$project_option_id" ]]; then
  issue_node_id="$(gh issue view "$issue_number" --json id --jq '.id')"

  item_id="$(gh api graphql -f query='
    query($projectId:ID!, $contentId:ID!) {
      node(id: $projectId) {
        ... on ProjectV2 {
          items(first: 100, contentId: $contentId) {
            nodes {
              id
            }
          }
        }
      }
    }
  ' -F projectId="$project_id" -F contentId="$issue_node_id" --jq '.data.node.items.nodes[0].id')"

  if [[ -n "$item_id" && "$item_id" != "null" ]]; then
    gh api graphql -f query='
      mutation($projectId:ID!, $itemId:ID!, $fieldId:ID!, $optionId:String!) {
        updateProjectV2ItemFieldValue(input: {
          projectId: $projectId
          itemId: $itemId
          fieldId: $fieldId
          value: { singleSelectOptionId: $optionId }
        }) {
          projectV2Item {
            id
          }
        }
      }
    ' \
      -F projectId="$project_id" \
      -F itemId="$item_id" \
      -F fieldId="$status_field_id" \
      -F optionId="$project_option_id" >/dev/null
  fi
fi

echo "Issue #$issue_number -> $target_status"

