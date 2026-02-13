<?php

test('dashboard theme audit map defines prioritized and actionable contrast targets', function () {
    $entries = config('dashboard-theme-audit.entries');

    expect($entries)->toBeArray()->not->toBeEmpty();

    foreach ($entries as $entry) {
        expect($entry)->toHaveKeys([
            'id',
            'area',
            'text_class',
            'surface_class',
            'severity',
            'recommended_class',
            'rationale',
        ]);

        expect($entry['severity'])->toBeIn(['high', 'medium', 'low']);
    }
});

test('dashboard audit entries align with current dashboard template hotspots', function () {
    $template = file_get_contents(resource_path('views/dashboard.blade.php'));
    $entries = config('dashboard-theme-audit.entries');

    expect($template)->not->toBeFalse();
    expect($entries)->toBeArray();

    foreach ($entries as $entry) {
        expect((string) $template)->toContain($entry['text_class']);
        expect((string) $template)->toContain($entry['surface_class']);
    }
});

test('high severity dashboard contrast issues are explicitly prioritized first', function () {
    $entries = config('dashboard-theme-audit.entries');

    expect($entries)->toBeArray()->not->toBeEmpty();
    expect($entries[0]['severity'])->toBe('high');
    expect($entries[0]['id'])->toBe('dashboard.table.header.labels');
});
