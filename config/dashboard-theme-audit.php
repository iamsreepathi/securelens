<?php

return [
    'entries' => [
        [
            'id' => 'dashboard.table.header.labels',
            'area' => 'Ingestion Freshness table header labels',
            'text_class' => 'dashboard-table-head',
            'surface_class' => 'dashboard-table-head-row',
            'severity' => 'high',
            'recommended_class' => 'dashboard-text-secondary',
            'rationale' => 'Header labels should use semantic dashboard text tokens so readability tuning can be centralized.',
        ],
        [
            'id' => 'dashboard.meta.copy.secondary',
            'area' => 'Dashboard supporting copy and meta labels',
            'text_class' => 'dashboard-copy',
            'surface_class' => 'dashboard-panel',
            'severity' => 'medium',
            'recommended_class' => 'dashboard-copy',
            'rationale' => 'Secondary copy should rely on shared dashboard tokens for consistent contrast across cards.',
        ],
        [
            'id' => 'dashboard.badge.no-data',
            'area' => 'No Data state badge in ingestion table',
            'text_class' => 'text-zinc-100',
            'surface_class' => 'bg-zinc-800',
            'severity' => 'medium',
            'recommended_class' => 'text-zinc-100',
            'rationale' => 'Status badge content is muted and visually close to surrounding table surfaces.',
        ],
        [
            'id' => 'dashboard.panel.borders',
            'area' => 'Card and panel border separators',
            'text_class' => 'dashboard-title',
            'surface_class' => 'dashboard-panel',
            'severity' => 'low',
            'recommended_class' => 'dashboard-panel',
            'rationale' => 'Panel boundaries and title contrast should be managed by semantic dashboard surface tokens.',
        ],
    ],
];
