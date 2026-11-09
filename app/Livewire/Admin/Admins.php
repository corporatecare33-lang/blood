<?php

namespace App\Livewire\Admin;

use App\Enums\Role;
use App\Models\User;
use App\Support\Mobile;
use App\Support\SiteCache;
use Livewire\Attributes\Computed;
use Livewire\Component;

/** Super admins promote registered members to admin, or demote them back to donor. */
class Admins extends Component
{
    public bool $showForm = false;

    public string $mobile = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
    }

    public function create(): void
    {
        $this->reset('mobile');
        $this->resetValidation();
        $this->showForm = true;
    }

    public function promote(): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        $this->mobile = Mobile::normalize($this->mobile);

        $this->validate(['mobile' => ['required', Mobile::RULE, 'exists:users,mobile']], [
            'mobile.exists' => 'এই নম্বরে কোনো সদস্য নিবন্ধিত নেই। আগে তাঁকে নিবন্ধন করতে বলুন।',
            'mobile.regex' => '১১ অঙ্কের মোবাইল নম্বর দিন (01XXXXXXXXX)।',
        ]);

        $user = User::where('mobile', $this->mobile)->firstOrFail();
        if ($user->isAdmin()) {
            $this->addError('mobile', $user->name.' আগেই অ্যাডমিন।');

            return;
        }

        $user->update(['role' => Role::Admin]);
        SiteCache::forgetDonorCount();
        $this->reset('mobile', 'showForm');
        unset($this->admins);
        $this->dispatch('notify', message: $user->name.' এখন অ্যাডমিন।');
    }

    public function demote(int $id): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        $user = User::where('role', Role::Admin)->findOrFail($id);
        $user->update(['role' => Role::Donor]);
        SiteCache::forgetDonorCount();
        unset($this->admins);
        $this->dispatch('notify', message: $user->name.' আবার ডোনার।');
    }

    #[Computed]
    public function admins()
    {
        return User::whereIn('role', [Role::Admin, Role::SuperAdmin])->with('district')->orderBy('role', 'desc')->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.admin.admins');
    }
}
