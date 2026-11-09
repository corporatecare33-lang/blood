<div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <x-ui.field label="রক্তের গ্রুপ" for="r-group" class="sm:w-44">
            <x-ui.select id="r-group" wire:model.live="group" placeholder="সব গ্রুপ">
                @foreach (\App\Enums\BloodGroup::cases() as $g)
                    <option value="{{ $g->slug() }}">{{ $g->value }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.field>
        <x-ui.field label="জেলা" for="r-district" class="sm:w-60">
            <x-ui.select id="r-district" wire:model.live="district" placeholder="সব জেলা">
                @foreach ($districts as $d)
                    <option value="{{ $d->id }}">{{ $d->bn_name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.field>
        <p class="text-[0.9375rem] text-ink-soft sm:ml-auto sm:pb-3" aria-live="polite">
            <span wire:loading.remove>{{ \App\Support\Bn::num($this->requests->total()) }}টি খোলা অনুরোধ</span>
            <span wire:loading class="inline-flex items-center gap-2"><x-ui.spinner />খোঁজা হচ্ছে…</span>
        </p>
    </div>

    <div wire:loading.class="opacity-60" class="mt-4 transition-opacity">
        @if ($this->requests->isEmpty())
            <x-site.empty-state :level="2" icon="clipboard-list" title="{{ $group || $district ? 'এই ফিল্টারে কোনো খোলা অনুরোধ নেই' : 'এই মুহূর্তে কোনো খোলা অনুরোধ নেই' }}">
                প্রয়োজনের সময় পার হওয়ার এক দিন পর অনুরোধ নিজে থেকেই তালিকা থেকে সরে যায়।
                <x-slot:action>
                    <x-ui.button :href="route('requests.create')">অনুরোধ পোস্ট করুন</x-ui.button>
                </x-slot:action>
            </x-site.empty-state>
        @else
            <h2 class="sr-only">খোলা অনুরোধের তালিকা</h2>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($this->requests as $request)
                    <x-site.request-card :request="$request" wire:key="req-{{ $request->id }}" />
                @endforeach
            </div>
            <div class="mt-6">{{ $this->requests->links() }}</div>
        @endif
    </div>
</div>
