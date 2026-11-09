{{--
    Button that asks before running a Livewire action, via the AlertDialog in layouts/admin.
    <x-admin.confirm action="delete(5)" title="ডোনার মুছবেন?" message="…" confirm="মুছুন" class="ui-menu-item is-danger">…</x-admin.confirm>
--}}
@props(['action', 'title', 'message' => null, 'confirm' => 'মুছুন', 'danger' => true])

<button type="button" {{ $attributes }}
    x-on:click="$dispatch('confirm', { title: @js($title), message: @js($message), confirm: @js($confirm), danger: @js($danger), run: () => $wire.{{ $action }} })">{{ $slot }}</button>
