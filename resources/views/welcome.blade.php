<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }} | Continuous Vulnerability Triage</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|manrope:500,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:rounded-md focus:bg-zinc-100 focus:px-3 focus:py-2 focus:text-sm focus:font-semibold focus:text-zinc-900">
            Skip to main content
        </a>

        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(50rem_30rem_at_15%_-5%,rgba(16,185,129,0.28),transparent),radial-gradient(42rem_28rem_at_85%_5%,rgba(59,130,246,0.22),transparent),linear-gradient(180deg,#09090b_0%,#0f172a_45%,#111827_100%)]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-[linear-gradient(to_bottom,rgba(255,255,255,0.08),transparent)]"></div>

            <div class="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col px-6 py-8 sm:px-10 lg:max-w-7xl lg:px-14 2xl:max-w-[90rem]">
                <header class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <span class="inline-flex size-9 items-center justify-center rounded-xl border border-emerald-300/40 bg-emerald-400/15 text-emerald-200">
                            <x-app-logo-icon class="size-5 fill-current" />
                        </span>
                        <span class="text-base font-semibold tracking-wide text-zinc-100">{{ config('app.name') }}</span>
                    </a>

                    <nav class="flex items-center gap-3 text-sm font-medium" aria-label="Primary">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ route('dashboard') }}" class="rounded-lg border border-zinc-700 bg-zinc-900/60 px-4 py-2 text-zinc-100 transition hover:border-zinc-500 hover:bg-zinc-800/70 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-emerald-300 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-zinc-300 transition hover:text-zinc-100 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-emerald-300 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950">Sign in</a>
                            @endauth
                        @endif
                    </nav>
                </header>

                <main id="main-content" class="mt-14 grid gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                    <section class="space-y-7">
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-300/30 bg-emerald-400/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-200">
                            Security velocity, without blind spots
                        </div>

                        <div class="space-y-5">
                            <h1 class="heading-display text-4xl leading-[1.05] font-extrabold text-balance text-white sm:text-5xl lg:text-6xl motion-safe:animate-fade-in-up">
                                Keep every project releasable with live risk clarity.
                            </h1>
                            <p class="max-w-2xl text-base leading-relaxed text-zinc-300 sm:text-lg">
                                SecureLens turns noisy vulnerability feeds into a fast triage system for engineering teams. Track risk by project, prioritize by severity, and recover ingestion failures before they impact release confidence.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ route('dashboard') }}" class="cta-primary">
                                        Open Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="cta-primary">
                                        Start Triage
                                    </a>
                                @endauth
                            @endif
                            <a href="#how-it-works" class="inline-flex items-center rounded-xl border border-zinc-600/80 bg-zinc-900/60 px-5 py-3 text-sm font-semibold text-zinc-100 transition hover:border-zinc-400 hover:bg-zinc-800/70 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-emerald-200 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950">
                                See How It Works
                            </a>
                        </div>

                        <dl class="grid gap-3 pt-3 sm:grid-cols-3">
                            <div class="rounded-xl border border-zinc-800/80 bg-zinc-900/50 p-4">
                                <dt class="text-xs uppercase tracking-[0.16em] text-zinc-400">Snapshot Integrity</dt>
                                <dd class="mt-2 text-xl font-semibold text-white">Idempotent Ingestion</dd>
                            </div>
                            <div class="rounded-xl border border-zinc-800/80 bg-zinc-900/50 p-4">
                                <dt class="text-xs uppercase tracking-[0.16em] text-zinc-400">Triage Focus</dt>
                                <dd class="mt-2 text-xl font-semibold text-white">Severity-First Views</dd>
                            </div>
                            <div class="rounded-xl border border-zinc-800/80 bg-zinc-900/50 p-4">
                                <dt class="text-xs uppercase tracking-[0.16em] text-zinc-400">Operational Safety</dt>
                                <dd class="mt-2 text-xl font-semibold text-white">Admin Recovery Controls</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="relative">
                        <div class="absolute -inset-4 -z-10 rounded-3xl bg-linear-to-br from-emerald-400/20 via-cyan-300/10 to-blue-500/20 blur-2xl"></div>
                        <div class="surface-panel rounded-3xl p-6 sm:p-7">
                            <div class="mb-6 flex items-center justify-between">
                                <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-300">Release Readiness Panel</h2>
                                <span class="rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-semibold text-emerald-200 motion-safe:animate-pulse motion-reduce:animate-none">Live</span>
                            </div>

                            <div class="space-y-4">
                                <div class="rounded-xl border border-zinc-700/80 bg-zinc-950/80 p-4">
                                    <p class="text-xs uppercase tracking-[0.16em] text-zinc-400">Critical Vulnerabilities</p>
                                    <p class="mt-2 text-3xl font-bold text-rose-300">03</p>
                                    <p class="mt-1 text-sm text-zinc-400">2 projects need immediate owner assignment.</p>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-xl border border-zinc-700/80 bg-zinc-950/80 p-4">
                                        <p class="text-xs uppercase tracking-[0.16em] text-zinc-400">Queue Health</p>
                                        <p class="mt-2 text-xl font-semibold text-amber-200">Transient Backlog</p>
                                        <p class="mt-1 text-sm text-zinc-400">Retry strategy active.</p>
                                    </div>
                                    <div class="rounded-xl border border-zinc-700/80 bg-zinc-950/80 p-4">
                                        <p class="text-xs uppercase tracking-[0.16em] text-zinc-400">Ingestion Signal</p>
                                        <p class="mt-2 text-xl font-semibold text-emerald-200">Fresh Snapshot</p>
                                        <p class="mt-1 text-sm text-zinc-400">Latest run 6 minutes ago.</p>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-zinc-700/80 bg-zinc-950/80 p-4" id="how-it-works">
                                    <p class="text-xs uppercase tracking-[0.16em] text-zinc-400">Triage Flow</p>
                                    <ol class="mt-3 space-y-2 text-sm text-zinc-200">
                                        <li>1. Ingest and normalize vulnerability snapshots.</li>
                                        <li>2. Prioritize by severity and project exposure.</li>
                                        <li>3. Resolve failures with admin replay controls.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>

                <section class="mt-14 rounded-2xl border border-zinc-800/80 bg-zinc-900/45 p-6 sm:p-8">
                    <div class="grid gap-6 lg:grid-cols-3">
                        <article class="space-y-2">
                            <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-zinc-300">Signal Over Noise</h3>
                            <p class="text-sm leading-relaxed text-zinc-400">Focus on what blocks releases now with clean severity distribution and ecosystem context.</p>
                        </article>
                        <article class="space-y-2">
                            <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-zinc-300">Operator Confidence</h3>
                            <p class="text-sm leading-relaxed text-zinc-400">Use guarded retry and token disable controls with audit trails for every privileged action.</p>
                        </article>
                        <article class="space-y-2">
                            <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-zinc-300">Incident Readiness</h3>
                            <p class="text-sm leading-relaxed text-zinc-400">Follow built-in runbooks to restore ingestion and queue health with predictable escalation paths.</p>
                        </article>
                    </div>
                </section>

                <section class="mt-8 grid gap-6 lg:grid-cols-2">
                    <article class="surface-panel p-6 sm:p-7">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Problem / Solution</p>
                        <h2 class="heading-display mt-3 text-2xl font-bold text-white sm:text-3xl">Stop treating vulnerability triage like a spreadsheet workflow.</h2>
                        <p class="mt-3 text-sm leading-relaxed text-zinc-300">
                            SecureLens gives your team a single flow: ingest, prioritize, assign, and recover from ingestion faults with operator-grade controls. You move faster because signal quality stays high even when upstream feeds get noisy.
                        </p>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-zinc-700/70 bg-zinc-950/45 p-4">
                                <p class="text-xs uppercase tracking-[0.16em] text-zinc-400">Time to triage</p>
                                <p class="mt-1 text-lg font-semibold text-zinc-100">&lt; 10 min</p>
                            </div>
                            <div class="rounded-xl border border-zinc-700/70 bg-zinc-950/45 p-4">
                                <p class="text-xs uppercase tracking-[0.16em] text-zinc-400">Failure recovery</p>
                                <p class="mt-1 text-lg font-semibold text-zinc-100">Admin replay ready</p>
                            </div>
                        </div>
                    </article>

                    <article class="surface-panel p-6 sm:p-7">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Trusted by Builders</p>
                        <h2 class="heading-display mt-3 text-2xl font-bold text-white sm:text-3xl">Engineers, platform teams, and security leads stay aligned.</h2>
                        <div class="mt-5 space-y-3">
                            <blockquote class="rounded-xl border border-zinc-700/70 bg-zinc-950/45 p-4 text-sm leading-relaxed text-zinc-200">
                                “We finally have one place to see release-blocking risk and ingestion health without context switching.”
                            </blockquote>
                            <blockquote class="rounded-xl border border-zinc-700/70 bg-zinc-950/45 p-4 text-sm leading-relaxed text-zinc-200">
                                “Queue and token incidents are now operational events, not fire drills.”
                            </blockquote>
                        </div>
                    </article>
                </section>

                <section class="mt-8 rounded-3xl border border-emerald-300/30 bg-linear-to-r from-emerald-400/20 via-cyan-400/15 to-blue-500/20 p-7 shadow-[0_16px_55px_rgba(20,184,166,0.25)] sm:p-9">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="max-w-2xl">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-100">Final CTA</p>
                            <h2 class="heading-display mt-2 text-3xl font-extrabold text-white sm:text-4xl">Upgrade your release confidence this sprint.</h2>
                            <p class="mt-2 text-sm leading-relaxed text-zinc-100/90">
                                Start with your highest-risk project and establish a triage workflow your entire engineering org can trust.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ route('dashboard') }}" class="cta-primary">
                                        Launch SecureLens
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="cta-primary">
                                        Launch SecureLens
                                    </a>
                                @endauth
                            @endif
                            <a href="#how-it-works" class="inline-flex items-center rounded-xl border border-zinc-300/45 bg-zinc-950/30 px-5 py-3 text-sm font-semibold text-zinc-100 transition hover:border-zinc-100/70 hover:bg-zinc-950/45 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-emerald-200 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950">
                                Review Flow
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>
