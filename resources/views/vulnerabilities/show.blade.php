<x-layouts::app :title="__('Vulnerability Details')">
    <div class="mx-auto w-full max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ $vulnerability->package_name }}</flux:heading>
                <flux:text>{{ __('Project: :project', ['project' => $vulnerability->project?->name]) }}</flux:text>
            </div>
            <flux:button :href="route('vulnerabilities.index')" variant="ghost" wire:navigate>
                {{ __('Back to List') }}
            </flux:button>
        </div>

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Severity') }}</flux:text>
                    <flux:heading size="lg">{{ str($vulnerability->severity())->title() }}</flux:heading>
                </div>
                <div>
                    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('CVSS Score') }}</flux:text>
                    <flux:heading size="lg">{{ $vulnerability->cvss_score ?? 'n/a' }}</flux:heading>
                </div>
                <div>
                    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Affected Version') }}</flux:text>
                    <flux:text>{{ $vulnerability->version }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Ecosystem') }}</flux:text>
                    <flux:text>{{ $vulnerability->ecosystem }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Source') }}</flux:text>
                    <flux:text>{{ $vulnerability->source }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Ingested At') }}</flux:text>
                    <flux:text>{{ $vulnerability->ingested_at?->toDayDateTimeString() ?? __('Unknown') }}</flux:text>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Remediation Guidance') }}</flux:heading>
            @if ($vulnerability->fixed_version)
                <flux:text class="mt-2">
                    {{ __('Upgrade :package to fixed version :version or later to remediate this issue.', ['package' => $vulnerability->package_name, 'version' => $vulnerability->fixed_version]) }}
                </flux:text>
            @else
                <flux:text class="mt-2">
                    {{ __('No fixed version is available yet. Track upstream advisories and apply compensating controls until a patched release is published.') }}
                </flux:text>
            @endif

            <div class="mt-4">
                <flux:button href="{{ $vulnerability->osv_url }}" target="_blank" icon-trailing="arrow-up-right">
                    {{ __('Open OSV Advisory') }}
                </flux:button>
            </div>
        </div>
    </div>
</x-layouts::app>
