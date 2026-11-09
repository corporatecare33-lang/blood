@props(['user', 'size' => 'size-8 text-xs'])
<span {{ $attributes->class("flex shrink-0 items-center justify-center rounded-full bg-crimson-soft font-semibold text-crimson-deep {$size}") }} aria-hidden="true">{{ $user->initials() }}</span>
