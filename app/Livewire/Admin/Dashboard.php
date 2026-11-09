<?php

namespace App\Livewire\Admin;

use App\Enums\BloodGroup;
use App\Enums\RequestStatus;
use App\Enums\Role;
use App\Models\BloodRequest;
use App\Models\User;
use App\Support\Bn;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;

class Dashboard extends Component
{
    /** Days shown in the trend chart and the period stats. */
    #[Url(except: 30)]
    public int $range = 30;

    public function updatedRange(): void
    {
        if (! in_array($this->range, [7, 30, 90], true)) {
            $this->range = 30;
        }
    }

    public function render()
    {
        $since = now()->startOfDay()->subDays($this->range - 1);

        $donors = User::donors()->count();
        $eligible = User::donors()->eligible()->count();
        $byGroup = User::donors()->selectRaw('blood_group, count(*) as total')->groupBy('blood_group')->pluck('total', 'blood_group');
        $readyByGroup = User::donors()->eligible()->selectRaw('blood_group, count(*) as total')->groupBy('blood_group')->pluck('total', 'blood_group');

        return view('livewire.admin.dashboard', [
            'stats' => [
                [
                    'label' => 'সক্রিয় ডোনার', 'icon' => 'users', 'value' => $donors, 'href' => route('admin.donors'),
                    'note' => '+'.Bn::num(User::donors()->where('created_at', '>=', $since)->count()).' জন গত '.Bn::num($this->range).' দিনে',
                ],
                [
                    'label' => 'এখন দিতে পারবেন', 'icon' => 'heart-handshake', 'value' => $eligible, 'href' => route('admin.donors', ['status' => 'eligible']),
                    'note' => 'সক্রিয় ডোনারের '.Bn::num($donors ? round($eligible / $donors * 100) : 0).'%',
                ],
                [
                    'label' => 'খোলা অনুরোধ', 'icon' => 'clipboard-list', 'value' => BloodRequest::active()->count(), 'href' => route('admin.requests'),
                    'note' => Bn::num(BloodRequest::active()->where('needed_at', '<', now()->addDay())->count()).'টি ২৪ ঘণ্টার মধ্যে দরকার',
                ],
                [
                    'label' => 'ডোনার পাওয়া গেছে', 'icon' => 'circle-check', 'value' => BloodRequest::where('status', RequestStatus::Fulfilled)->where('updated_at', '>=', $since)->count(),
                    'href' => route('admin.requests', ['status' => 'fulfilled']),
                    'note' => 'গত '.Bn::num($this->range).' দিনে পূরণ হওয়া অনুরোধ',
                ],
            ],
            'trend' => [
                'donors' => $this->series(User::where('role', Role::Donor), $since),
                'requests' => $this->series(BloodRequest::query(), $since),
            ],
            'groups' => collect(BloodGroup::cases())->map(fn ($g) => [
                'group' => $g->value,
                'slug' => $g->slug(),
                'total' => (int) ($byGroup[$g->value] ?? 0),
                'ready' => (int) ($readyByGroup[$g->value] ?? 0),
            ]),
            'urgent' => BloodRequest::active()->with('district')->orderBy('needed_at')->limit(5)->get(),
            'latest' => User::where('role', Role::Donor)->with('district')->latest()->limit(6)->get(),
        ]);
    }

    /**
     * New rows per day (7 or 30 days) or per week (90 days), oldest first.
     *
     * @return list<array{label: string, tip: string, value: int}>
     */
    private function series(Builder $query, $since): array
    {
        $counts = $query->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $weekly = $this->range > 30;
        $step = $weekly ? 7 : 1;
        $start = CarbonImmutable::parse($since);
        $points = [];

        for ($offset = 0; $offset < $this->range; $offset += $step) {
            $from = $start->addDays($offset);
            $to = $from->addDays(min($step, $this->range - $offset) - 1);
            $value = 0;
            for ($d = $from; $d <= $to; $d = $d->addDay()) {
                $value += (int) ($counts[$d->toDateString()] ?? 0);
            }

            $fromLabel = Bn::num($from->locale('bn')->translatedFormat('j F'));
            $points[] = [
                'label' => $fromLabel,
                'tip' => $weekly ? $fromLabel.' – '.Bn::num($to->locale('bn')->translatedFormat('j F')) : Bn::num($from->locale('bn')->translatedFormat('l, j F')),
                'value' => $value,
            ];
        }

        return $points;
    }
}
