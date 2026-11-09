{{--
    Sidebar body shared by the desktop rail and the mobile drawer (included from layouts/admin).
    $rail = true: desktop copy, which shrinks to icons when <html> has .sidebar-collapsed (lg:collapsed:* utilities).
--}}
<div class="flex items-center gap-2 p-2 lg:collapsed:justify-center">
    <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex min-w-0 flex-1 items-center gap-2.5 rounded-lg p-1.5 hover:bg-sidebar-accent lg:collapsed:flex-none">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-crimson text-white" aria-hidden="true">
            <x-lucide-droplet class="size-4 fill-white" />
        </span>
        <span class="min-w-0 leading-tight lg:collapsed:sr-only">
            <span class="block truncate font-display text-[1.0625rem] font-bold">রক্তদূত</span>
            <span class="block truncate text-xs text-muted-foreground">অ্যাডমিন প্যানেল</span>
        </span>
    </a>
    @unless ($rail)
        <button type="button" x-on:click="drawer = false" class="flex size-8 shrink-0 items-center justify-center rounded-md text-ink-soft hover:bg-sidebar-accent hover:text-ink">
            <x-lucide-x class="size-4" aria-hidden="true" /><span class="sr-only">মেনু বন্ধ করুন</span>
        </button>
    @endunless
</div>

<nav class="flex-1 overflow-y-auto px-2 pb-2" aria-label="অ্যাডমিন">
    @foreach ($groups as $heading => $items)
        <div class="pt-3">
            <p class="px-2.5 pb-1 text-xs font-medium text-muted-foreground lg:collapsed:sr-only">{{ $heading }}</p>
            <ul class="grid gap-0.5">
                @foreach ($items as [$label, $route, $icon, $badge])
                    <li>
                        <a href="{{ route($route) }}" wire:navigate
                           @if ($route === 'admin.dashboard') wire:current.exact="bg-white! text-ink! shadow-xs" @else wire:current="bg-white! text-ink! shadow-xs" @endif
                           @if ($rail) title="{{ $label }}" @endif
                           class="relative flex h-9 items-center gap-2.5 rounded-md px-2.5 text-sm font-medium text-ink-soft transition-colors hover:bg-sidebar-accent hover:text-ink lg:collapsed:justify-center lg:collapsed:px-0">
                            <x-dynamic-component :component="'lucide-'.$icon" class="size-4 shrink-0" aria-hidden="true" />
                            <span class="truncate lg:collapsed:sr-only">{{ $label }}</span>
                            @if ($badge)
                                <span class="ml-auto rounded-full bg-crimson px-1.5 text-xs font-semibold text-white tabular-nums lg:collapsed:absolute lg:collapsed:top-0.5 lg:collapsed:right-0.5 lg:collapsed:size-2 lg:collapsed:p-0 lg:collapsed:text-[0]">
                                    {{ \App\Support\Bn::num($badge) }}<span class="sr-only"> টি খোলা</span>
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>

<div class="border-t border-sidebar-border p-2">
    <x-ui.dropdown :align="$rail ? 'right-end' : 'top-start'" width="w-60">
        <x-slot:trigger>
            <button type="button" aria-haspopup="menu" class="flex w-full items-center gap-2.5 rounded-lg p-1.5 text-left hover:bg-sidebar-accent lg:collapsed:justify-center">
                <x-ui.avatar :user="$user" />
                <span class="min-w-0 flex-1 leading-tight lg:collapsed:sr-only">
                    <span class="block truncate text-sm font-semibold">{{ $user->name }}</span>
                    <span class="block truncate text-xs text-muted-foreground tabular-nums" lang="en">{{ $user->mobile }}</span>
                </span>
                <x-lucide-chevrons-up-down class="size-4 shrink-0 text-ink-soft lg:collapsed:hidden" aria-hidden="true" />
            </button>
        </x-slot:trigger>

        <div class="flex items-center gap-2.5 px-2 py-1.5">
            <x-ui.avatar :user="$user" />
            <div class="min-w-0 leading-tight">
                <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                <p class="truncate text-xs text-muted-foreground">{{ $user->role->label() }}</p>
            </div>
        </div>
        <div class="-mx-1 my-1 h-px bg-border"></div>
        <a href="{{ route('admin.profile') }}" wire:navigate class="ui-menu-item"><x-lucide-user-round-cog aria-hidden="true" />আমার অ্যাকাউন্ট</a>
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="ui-menu-item"><x-lucide-globe aria-hidden="true" />সাইট দেখুন</a>
        <div class="-mx-1 my-1 h-px bg-border"></div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="ui-menu-item is-danger"><x-lucide-log-out aria-hidden="true" />লগআউট</button>
        </form>
    </x-ui.dropdown>
</div>
