<?php

namespace Tests\Feature;

use App\Contracts\SmsGateway;
use App\Enums\BloodGroup;
use App\Enums\Role;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Auth\RegisterForm;
use App\Livewire\Donors\Search;
use App\Livewire\Requests\CreateForm;
use App\Models\BloodRequest;
use App\Models\District;
use App\Models\User;
use Database\Seeders\GeoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DonorFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeoSeeder::class);
    }

    public function test_donor_can_register(): void
    {
        $dhaka = District::where('name', 'Dhaka')->first();

        Livewire::test(RegisterForm::class)
            ->set('name', 'Rahim Uddin')
            ->set('mobile', '+880 1712-345678')
            ->set('gender', 'Male')
            ->set('division_id', $dhaka->division_id)
            ->set('district_id', $dhaka->id)
            ->set('date_of_birth', now()->subYears(25)->toDateString())
            ->set('password', 'secret-pass')
            ->set('password_confirmation', 'secret-pass')
            ->set('blood_group', 'O+')
            ->set('occupation', 'Student')
            ->set('agree', true)
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('account'));

        $user = User::firstWhere('mobile', '01712345678');
        $this->assertNotNull($user);
        $this->assertSame(BloodGroup::OPos, $user->blood_group);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_rejects_minors_and_bad_numbers(): void
    {
        Livewire::test(RegisterForm::class)
            ->set('mobile', '12345')
            ->set('date_of_birth', now()->subYears(16)->toDateString())
            ->call('register')
            ->assertHasErrors(['mobile', 'date_of_birth', 'agree', 'blood_group']);
    }

    public function test_login_with_mobile_and_password(): void
    {
        $user = User::factory()->create(['mobile' => '01812345678']);

        Livewire::test(LoginForm::class)
            ->set('mobile', '01812345678')->set('password', 'wrong')->call('login')
            ->assertHasErrors('mobile');

        Livewire::test(LoginForm::class)
            ->set('mobile', '01812345678')->set('password', 'password')->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_search_hides_recent_donors_and_supports_compatibility(): void
    {
        $district = District::first();
        $eligible = User::factory()->create(['blood_group' => 'O-', 'district_id' => $district->id, 'division_id' => $district->division_id]);
        $recent = User::factory()->create(['blood_group' => 'O-', 'district_id' => $district->id, 'division_id' => $district->division_id, 'last_donated_at' => now()->subDays(10)]);
        $aPos = User::factory()->create(['blood_group' => 'A+', 'district_id' => $district->id, 'division_id' => $district->division_id]);

        $this->actingAs($aPos);

        Livewire::test(Search::class)
            ->set('group', 'o-negative')
            ->assertSee($eligible->mobile)
            ->assertDontSee($recent->mobile);

        Livewire::test(Search::class)
            ->set('group', 'a-positive')
            ->assertDontSee($eligible->mobile)
            ->set('compatible', true)
            ->assertSee($eligible->mobile);
    }

    public function test_filter_pills_remove_their_filter(): void
    {
        $district = District::first();
        $this->actingAs(User::factory()->create());

        Livewire::test(Search::class)
            ->set('group', 'a-positive')
            ->set('compatible', true)
            ->set('division_id', $district->division_id)
            ->set('district_id', $district->id)
            ->assertSee('মানানসই গ্রুপসহ')
            ->assertSee($district->bn_name)
            ->call('removeFilter', 'group')
            ->assertSet('group', '')
            ->assertSet('compatible', false)
            ->call('removeFilter', 'area')
            ->assertSet('division_id', null)
            ->assertSet('district_id', null);
    }

    public function test_anyone_can_post_a_blood_request(): void
    {
        $district = District::first();

        Livewire::test(CreateForm::class)
            ->set('patient_name', 'Ayesha')
            ->set('blood_group', 'B+')
            ->set('bags', 2)
            ->set('needed_at', now()->addHours(5)->format('Y-m-d\TH:i'))
            ->set('hospital', 'Dhaka Medical College Hospital')
            ->set('district_id', (string) $district->id)
            ->set('contact_name', 'Karim')
            ->set('contact_mobile', '01912345678')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('requests.index'));

        $this->assertSame(1, BloodRequest::active()->count());
        $this->get('/blood-requests')->assertSee('Dhaka Medical College Hospital');
    }

    public function test_password_reset_with_otp(): void
    {
        $user = User::factory()->create(['mobile' => '01512345678']);
        $sent = null;
        $this->app->instance(SmsGateway::class, new class($sent) implements SmsGateway
        {
            public function __construct(public &$sent) {}

            public function send(string $mobile, string $message): void
            {
                $this->sent = $message;
            }
        });

        $component = Livewire::test(ForgotPassword::class)->set('mobile', '01512345678')->call('sendCode')->assertSet('step', 'verify');
        preg_match('/\d{6}/', $sent, $m);

        $component->set('code', '000000')->set('password', 'new-password')->set('password_confirmation', 'new-password')
            ->call('resetPassword')->assertHasErrors('code');

        $component->set('code', $m[0])->call('resetPassword')->assertHasNoErrors();
        $this->assertTrue(password_verify('new-password', $user->fresh()->password));
    }

    public function test_admin_pages_load_for_admins_only(): void
    {
        $donor = User::factory()->create();
        $this->actingAs($donor)->get('/admin')->assertForbidden();
        // Each account signs in on its own session (auth.session pins a session to one password).
        $this->flushSession();

        $admin = User::factory()->create(['role' => Role::Admin]);
        foreach (['/admin', '/admin?range=7', '/admin?range=90', '/admin/donors', '/admin/blood-requests', '/admin/thanas', '/admin/blog', '/admin/blog/new', '/admin/gallery', '/admin/videos', '/admin/about', '/admin/settings', '/admin/profile'] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
        $this->actingAs($admin)->get('/admin/admins')->assertForbidden();

        $super = User::factory()->create(['role' => Role::SuperAdmin]);
        $this->flushSession();
        $this->actingAs($super)->get('/admin/admins')->assertOk();
    }
}
