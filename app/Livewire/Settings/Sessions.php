<?php

namespace App\Livewire\Settings;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Sessions extends Component
{
    public string $current_password = '';

    /**
     * @var array<int, array{id: string, ip_address: string|null, user_agent: string|null, last_active_at: string, is_current: bool}>
     */
    public array $sessions = [];

    public function mount(): void
    {
        $this->loadSessions();
    }

    public function logoutOtherSessions(): void
    {
        $validated = $this->validate([
            'current_password' => ['required', 'string', 'current_password:web'],
        ]);

        Auth::logoutOtherDevices($validated['current_password']);

        DB::table('sessions')
            ->where('user_id', auth()->id())
            ->where('id', '!=', session()->getId())
            ->delete();

        Log::info('security.sessions.logout_other_devices', [
            'user_id' => auth()->id(),
            'retained_session_id' => session()->getId(),
        ]);

        $this->reset('current_password');
        $this->loadSessions();
        $this->dispatch('sessions-updated');
    }

    protected function loadSessions(): void
    {
        if (! Schema::hasTable('sessions')) {
            $this->sessions = [];

            return;
        }

        $currentSessionId = session()->getId();

        $this->sessions = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->map(function (object $session) use ($currentSessionId): array {
                return [
                    'id' => (string) $session->id,
                    'ip_address' => $session->ip_address !== null ? (string) $session->ip_address : null,
                    'user_agent' => $session->user_agent !== null ? (string) $session->user_agent : null,
                    'last_active_at' => CarbonImmutable::createFromTimestamp((int) $session->last_activity)
                        ->toDateTimeString(),
                    'is_current' => (string) $session->id === $currentSessionId,
                ];
            })
            ->values()
            ->all();
    }
}
