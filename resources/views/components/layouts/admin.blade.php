{{--
    Admin panel shell — shadcn "inset sidebar" layout.
    <x-layouts.admin title="ডোনার" description="…"> … </x-layouts.admin>
    Desktop: sidebar collapses to an icon rail (remembered per browser). Mobile: the sidebar opens as a drawer.
--}}
@props(['title', 'description' => null])
@php
    \App\Support\Seo::make()->title($title.' — অ্যাডমিন')->description('রক্তদূত অ্যাডমিন প্যানেল।')->noindex();
    $user = auth()->user();
    $openRequests = \App\Models\BloodRequest::active()->count();

    $groups = [
        'প্যানেল' => [
            ['সারসংক্ষেপ', 'admin.dashboard', 'layout-dashboard', null],
        ],
        'ব্যবস্থাপনা' => [
            ['ডোনার', 'admin.donors', 'users', null],
            ['রক্তের অনুরোধ', 'admin.requests', 'clipboard-list', $openRequests],
        ],
        'কনটেন্ট' => [
            ['গ্যালারি', 'admin.gallery', 'images', null],
            ['আমাদের সম্পর্কে', 'admin.about', 'file-text', null],
            ['থানা', 'admin.thanas', 'map-pin', null],
        ],
        'সেটিংস' => array_values(array_filter([
            ['সাইট সেটিংস', 'admin.settings', 'settings', null],
            $user->isSuperAdmin() ? ['অ্যাডমিন', 'admin.admins', 'shield-check', null] : null,
        ])),
    ];
@endphp

<x-layouts.admin-base body-class="bg-sidebar">
    <div x-data="{ drawer: false }" x-on:livewire:navigating.window="drawer = false" x-on:keydown.escape.window="drawer = false" class="min-h-dvh">

        {{-- Sidebar (desktop) --}}
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col transition-[width] duration-200 ease-out lg:flex lg:collapsed:w-[3.75rem]" aria-label="অ্যাডমিন মেনু">
            @include('components.admin.sidebar-content', ['groups' => $groups, 'user' => $user, 'rail' => true])
        </aside>

        {{-- Sidebar (mobile drawer) --}}
        <template x-teleport="body">
            <div x-show="drawer" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="অ্যাডমিন মেনু">
                <div x-show="drawer" x-transition.opacity class="absolute inset-0 bg-ink/45" x-on:click="drawer = false"></div>
                <div x-show="drawer" x-trap.noscroll="drawer"
                     x-transition:enter="transition duration-250 ease-out" x-transition:enter-start="-translate-x-full"
                     x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="-translate-x-full"
                     class="absolute inset-y-0 left-0 flex w-[min(18rem,85vw)] flex-col bg-sidebar shadow-pop">
                    @include('components.admin.sidebar-content', ['groups' => $groups, 'user' => $user, 'rail' => false])
                </div>
            </div>
        </template>

        {{-- Workspace --}}
        <div class="flex min-h-dvh flex-col transition-[padding] duration-200 ease-out lg:py-2 lg:pr-2 lg:pl-64 lg:collapsed:pl-[3.75rem]">
            <div class="flex flex-1 flex-col bg-white lg:rounded-xl lg:border lg:border-sidebar-border lg:shadow-xs">
                <header class="sticky top-0 z-20 flex h-14 shrink-0 items-center gap-2 border-b border-border bg-white/90 px-3 backdrop-blur sm:px-4 lg:rounded-t-xl">
                    <button type="button" x-on:click="drawer = true" class="flex size-8 items-center justify-center rounded-md text-ink-soft hover:bg-muted hover:text-ink lg:hidden">
                        <x-lucide-panel-left class="size-4" aria-hidden="true" /><span class="sr-only">মেনু খুলুন</span>
                    </button>
                    <button type="button"
                        x-on:click="let c = document.documentElement.classList.toggle('sidebar-collapsed'); try { localStorage.setItem('admin-sidebar-collapsed', c ? '1' : '0') } catch (e) {}"
                        class="hidden size-8 items-center justify-center rounded-md text-ink-soft hover:bg-muted hover:text-ink lg:flex">
                        <x-lucide-panel-left class="size-4" aria-hidden="true" /><span class="sr-only">সাইডবার ছোট/বড় করুন</span>
                    </button>
                    <span class="mx-1 h-4 w-px bg-border" aria-hidden="true"></span>
                    <nav aria-label="ব্রেডক্রাম্ব" class="min-w-0 flex-1">
                        <ol class="flex items-center gap-1.5 text-sm">
                            <li class="hidden sm:block"><a href="{{ route('admin.dashboard') }}" wire:navigate class="text-muted-foreground hover:text-ink">অ্যাডমিন</a></li>
                            <li class="hidden text-muted-foreground sm:block" aria-hidden="true"><x-lucide-chevron-right class="size-3.5" /></li>
                            <li class="truncate font-medium text-ink" aria-current="page">{{ $title }}</li>
                        </ol>
                    </nav>
                    <x-ui.button :href="route('home')" variant="outline" size="xs" class="shadow-xs" target="_blank" :navigate="false">
                        <x-lucide-external-link aria-hidden="true" /><span class="hidden sm:inline">সাইট দেখুন</span><span class="sr-only sm:hidden">সাইট দেখুন</span>
                    </x-ui.button>
                </header>

                <main id="content" tabindex="-1" class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    <div class="mx-auto w-full max-w-7xl">
                        <div data-intro class="mb-6 flex flex-wrap items-end justify-between gap-x-6 gap-y-3">
                            <div class="min-w-0">
                                <h1 class="font-display text-2xl font-bold sm:text-[1.75rem]">{{ $title }}</h1>
                                @if ($description)<p class="mt-1 text-sm text-muted-foreground sm:text-[0.9375rem]">{{ $description }}</p>@endif
                            </div>
                        </div>
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </div>

    <x-admin.confirm-host />
</x-layouts.admin-base>
