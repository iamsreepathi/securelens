<?php

test('dashboard template includes hierarchy cues for cards, tables, and ecosystem blocks', function () {
    $template = file_get_contents(resource_path('views/dashboard.blade.php'));

    expect($template)->not->toBeFalse();
    expect((string) $template)->toContain('Severity Snapshot');
    expect((string) $template)->toContain('Immediate action required');
    expect((string) $template)->toContain('transition-colors hover:bg-zinc-900/55');
    expect((string) $template)->toContain('Ecosystem Share');
    expect((string) $template)->toContain('Total: :count');
});
