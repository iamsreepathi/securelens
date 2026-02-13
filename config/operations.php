<?php

return [
    'runbooks' => [
        'ingestion_webhook_failures' => [
            'title' => 'Webhook Failure Triage',
            'version' => '2026-02-13',
            'objective' => 'Restore ingestion continuity when webhook snapshots are failing authentication, validation, or queue processing.',
            'triage_steps' => [
                'Confirm latest failures in Admin > Ingestion Failures and identify the affected project, source, and exception pattern.',
                'Validate that webhook token state is correct; if compromise is suspected, disable active tokens and rotate credentials.',
                'Check ingestion payload shape against the OpenAPI contract and verify snapshot_id/source consistency.',
                'Use admin retry/requeue for transient infrastructure failures once upstream health is restored.',
            ],
            'escalation' => [
                'Escalate to security lead if unauthorized token usage, suspicious source traffic, or secret leakage is detected.',
                'Escalate to platform engineering if failures persist after token remediation and replay attempts.',
            ],
            'rollback' => [
                'Temporarily disable compromised tokens and issue replacement tokens to trusted pipelines only.',
                'Suspend upstream ingestion source until schema/auth mismatch is corrected.',
            ],
        ],
        'queue_backlog_and_failure_recovery' => [
            'title' => 'Queue Backlog and Failure Recovery',
            'version' => '2026-02-13',
            'objective' => 'Reduce backlog, recover failed jobs safely, and return queue throughput to steady state.',
            'triage_steps' => [
                'Check Admin > Operations for pending depth, delayed jobs, retrying jobs, and failed/dead-letter trends.',
                'Prioritize ingestion queue if vulnerability freshness is at risk; inspect max attempts and dead-letter volume.',
                'Retry only dead-letter jobs with known transient root causes and valid payload integrity.',
                'If backlog growth exceeds worker capacity, scale workers and monitor drain-down over 15 minute intervals.',
            ],
            'escalation' => [
                'Escalate to SRE/on-call manager when queue backlog continues to grow after scaling and retry controls.',
                'Escalate to application owners for repeated job-level logic exceptions tied to a specific source.',
            ],
            'rollback' => [
                'Pause non-critical queue producers to protect ingestion processing.',
                'Revert recent queue-worker configuration changes if they correlate with throughput regression.',
            ],
        ],
    ],
];
