<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-zinc-900 dark:bg-[radial-gradient(60rem_24rem_at_-5%_-10%,rgba(16,185,129,0.18),transparent),radial-gradient(45rem_20rem_at_105%_0%,rgba(59,130,246,0.18),transparent),linear-gradient(180deg,#09090b_0%,#111827_65%,#0f172a_100%)] dark:text-zinc-100">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-white/95 backdrop-blur-md dark:border-zinc-700/70 dark:bg-zinc-950/85">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="gap-1.5">
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="users" :href="route('teams.index')" :current="request()->routeIs('teams.*')" wire:navigate>
                        {{ __('Teams') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="folder" :href="route('projects.index')" :current="request()->routeIs('projects.*')" wire:navigate>
                        {{ __('Projects') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="shield-exclamation" :href="route('vulnerabilities.index')" :current="request()->routeIs('vulnerabilities.*')" wire:navigate>
                        {{ __('Vulnerabilities') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if (auth()->user()->isAdmin())
                    <flux:sidebar.group :heading="__('Admin')" class="grid">
                        <flux:sidebar.item icon="chart-bar" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                            {{ __('Operations') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="exclamation-triangle" :href="route('admin.ingestion-failures.index')" :current="request()->routeIs('admin.ingestion-failures.*')" wire:navigate>
                            {{ __('Ingestion Failures') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.audit-logs.index')" :current="request()->routeIs('admin.audit-logs.*')" wire:navigate>
                            {{ __('Audit Logs') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="book-open-text" :href="route('admin.runbooks.index')" :current="request()->routeIs('admin.runbooks.*')" wire:navigate>
                            {{ __('Runbooks') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav class="border-t border-zinc-200/80 pt-3 dark:border-zinc-800/80">
                <flux:sidebar.item icon="home" :href="route('home')" wire:navigate>
                    {{ __('SecureLens Site') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="credit-card" :href="route('billing.edit')" wire:navigate>
                    {{ __('Billing') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="border-b border-zinc-200/80 bg-white/95 backdrop-blur-md dark:border-zinc-800/80 dark:bg-zinc-950/80 lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
