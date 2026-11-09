{{-- Desktop breadcrumb trail from Seo::breadcrumbs(). Hidden on mobile, where the app bar's back button does this job. --}}
@php($crumbs = \App\Support\Seo::make()->breadcrumbs)

@if (count($crumbs) > 1)
    <nav aria-label="ব্রেডক্রাম্ব" {{ $attributes->class('hidden md:block') }}>
        <ol class="flex flex-wrap items-center gap-1.5 text-sm text-muted-foreground">
            @foreach ($crumbs as $crumb)
                <li class="flex items-center gap-1.5">
                    @if (! $loop->last)
                        <a href="{{ $crumb['url'] }}" wire:navigate class="hover:text-crimson hover:underline">{{ $crumb['name'] }}</a>
                        <x-lucide-chevron-right class="size-3.5" aria-hidden="true" />
                    @else
                        <span aria-current="page" class="text-ink">{{ $crumb['name'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
