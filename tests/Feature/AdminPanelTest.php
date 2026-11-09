<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\Role;
use App\Livewire\Admin\Donors;
use App\Livewire\Admin\Login;
use App\Livewire\Admin\Profile;
use App\Livewire\Admin\Requests;
use App\Livewire\Admin\Settings;
use App\Models\BloodRequest;
use App\Models\District;
use App\Models\User;
use App\Support\Site;
use Database\Seeders\GeoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeoSeeder::class);
    }

    private function bloodRequest(array $overrides = []): BloodRequest
    {
        return BloodRequest::create($overrides + [
            'patient_name' => 'Karim', 'blood_group' => 'A+', 'bags' => 2, 'needed_at' => now()->addHours(5),
            'hospital' => 'Dhaka Medical', 'district_id' => District::first()->id,
            'contact_name' => 'Rafiq', 'contact_mobile' => '01711111111', 'status' => RequestStatus::Open,
        ]);
    }

    public function test_admin_login_page_and_redirects(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('অ্যাডমিন প্যানেলে লগইন')->assertSee('noindex, nofollow', false);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/admin/login')->assertRedirect(route('admin.dashboard'));
    }

    public function test_only_admins_can_sign_in_to_the_panel(): void
    {
        User::factory()->create(['mobile' => '01812345678']);
        Livewire::test(Login::class)
            ->set('mobile', '01812345678')->set('password', 'password')->call('login')
            ->assertHasErrors('mobile');
        $this->assertGuest();

        Livewire::test(Login::class)
            ->set('mobile', '01812345679')->set('password', 'password')->call('login')
            ->assertHasErrors('mobile');
        $this->assertGuest();

        $admin = User::factory()->admin()->create(['mobile' => '01912345678']);
        Livewire::test(Login::class)
            ->set('mobile', '+880 1912-345678')->set('password', 'password')->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_logout_returns_to_admin_login(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_settings_change_the_public_site(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Settings::class)
            ->set('email', 'not-an-email')->call('save')->assertHasErrors('email');

        Livewire::test(Settings::class)
            ->set('email', 'help@example.org')
            ->set('conditions', ['স্বেচ্ছায় রক্ত দিবেন।', '  ', 'নতুন শর্ত।'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('help@example.org', Site::email());
        $this->assertSame(['স্বেচ্ছায় রক্ত দিবেন।', 'নতুন শর্ত।'], Site::conditions());
        $this->get('/terms')->assertSee('help@example.org')->assertSee('নতুন শর্ত।');

        // Saving the original value again drops the override instead of freezing it.
        Livewire::test(Settings::class)->set('email', config('bandhan.email'))->call('save');
        $this->assertDatabaseMissing('site_settings', ['key' => 'email']);
    }

    public function test_admin_edits_and_filters_donors(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $donor = User::factory()->create(['name' => 'Old Name', 'blood_group' => 'B+']);
        User::factory()->create(['blood_group' => 'O-']);

        Livewire::test(Donors::class)
            ->set('group', 'b-positive')
            ->assertSee('Old Name')
            ->call('edit', $donor->id)
            ->assertSet('showEditor', true)
            ->set('name', 'New Name')
            ->set('is_active', false)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showEditor', false);

        $donor->refresh();
        $this->assertSame('New Name', $donor->name);
        $this->assertFalse($donor->is_active);
    }

    public function test_bulk_actions_and_delete_permissions(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $donors = User::factory()->count(3)->create();

        Livewire::test(Donors::class)
            ->set('selected', $donors->take(2)->pluck('id')->map(fn ($id) => (string) $id)->all())
            ->call('bulkSetActive', false);
        $this->assertSame(2, User::where('role', Role::Donor)->where('is_active', false)->count());

        Livewire::test(Donors::class)->call('delete', $donors->first()->id)->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => Role::SuperAdmin]));
        Livewire::test(Donors::class)->call('delete', $donors->first()->id);
        $this->assertModelMissing($donors->first());
    }

    public function test_donor_csv_export(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        User::factory()->create(['name' => 'Export Me']);

        Livewire::test(Donors::class)->call('export')->assertFileDownloaded('bandhan-donors-'.now()->format('Y-m-d').'.csv');
    }

    public function test_request_status_and_edit(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $request = $this->bloodRequest();

        Livewire::test(Requests::class)
            ->assertSee('Karim')
            ->call('setStatus', $request->id, 'fulfilled');
        $this->assertSame(RequestStatus::Fulfilled, $request->fresh()->status);

        Livewire::test(Requests::class)
            ->set('status', 'all')
            ->call('edit', $request->id)
            ->set('hospital', 'Mitford Hospital')
            ->set('request_status', 'closed')
            ->call('save')
            ->assertHasNoErrors();

        $request->refresh();
        $this->assertSame('Mitford Hospital', $request->hospital);
        $this->assertSame(RequestStatus::Closed, $request->status);
    }

    public function test_admin_changes_own_password(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(Profile::class)
            ->set('current_password', 'wrong')->set('password', 'brand-new-pass-1')->set('password_confirmation', 'brand-new-pass-1')
            ->call('savePassword')->assertHasErrors('current_password');

        Livewire::test(Profile::class)
            ->set('current_password', 'password')->set('password', 'brand-new-pass-1')->set('password_confirmation', 'brand-new-pass-1')
            ->call('savePassword')->assertHasNoErrors();

        $this->assertTrue(password_verify('brand-new-pass-1', $admin->fresh()->password));
    }

    public function test_demoted_admin_loses_access(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/admin/donors')->assertOk();

        $admin->update(['role' => Role::Donor]);
        $this->flushSession();
        $this->actingAs($admin->fresh())->get('/admin/donors')->assertForbidden();
    }
}
