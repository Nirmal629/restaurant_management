<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_employee_cannot_login(): void
    {
        $user = User::factory()->create(['email' => 'inactive@example.test', 'password' => 'password']);
        $branch = Branch::create(['code' => 'ICH-01', 'name' => 'Ichapur Main Branch']);
        $role = Role::create(['name' => 'Waiter']);
        Employee::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'branch_id' => $branch->id,
            'employee_code' => 'EMP-999',
            'name' => 'Inactive Staff',
            'phone' => '9999999999',
            'status' => 'inactive',
        ]);

        $this->post('/login', ['email' => 'inactive@example.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_permission_middleware_blocks_missing_permission(): void
    {
        $this->actingAsEmployeeWithPermissions([['Inventory', 'View']]);

        $this->get('/purchases')->assertForbidden();
    }

    public function test_dashboard_renders_for_authenticated_employee(): void
    {
        $this->actingAsEmployeeWithPermissions([['Dashboard', 'View']]);

        $this->get('/dashboard')->assertOk();
    }

    public function test_dashboard_requires_dashboard_permission(): void
    {
        $this->actingAsEmployeeWithPermissions([['Inventory', 'View']]);

        $this->get('/dashboard')->assertForbidden();
    }

    public function test_dashboard_menu_only_shows_with_dashboard_permission(): void
    {
        $this->actingAsEmployeeWithPermissions([['Inventory', 'View']]);

        $this->get('/inventory')
            ->assertOk()
            ->assertDontSee('Dashboard');
    }

    public function test_employee_creation_creates_login_user_with_default_password(): void
    {
        $this->actingAsEmployeeWithPermissions([['Employees', 'Create']], 'Staff Admin');

        $this->postJson('/employees', [
            'name' => 'New Staff',
            'phone' => '9898989898',
            'email' => 'new.staff@example.test',
            'role' => 'Staff Admin',
            'shift' => 'fullday',
            'joiningDate' => now()->toDateString(),
        ])->assertCreated();

        $user = User::where('email', 'new.staff@example.test')->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertDatabaseHas('employees', [
            'email' => 'new.staff@example.test',
            'user_id' => $user->id,
        ]);
    }

    public function test_authenticated_employee_can_change_password(): void
    {
        [$user] = $this->actingAsEmployeeWithPermissions([['Inventory', 'View']]);

        $this->put('/change-password', [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    public function test_authenticated_employee_can_view_and_update_profile(): void
    {
        [$user, $employee] = $this->actingAsEmployeeWithPermissions([['Inventory', 'View']]);

        $this->get('/profile')
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSee($employee->employee_code);

        $this->put('/profile', [
            'name' => 'Updated Operator',
            'email' => 'updated.operator@example.test',
            'phone' => '9876500001',
            'address' => 'Station Road, Ichapur',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Operator',
            'email' => 'updated.operator@example.test',
        ]);
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'Updated Operator',
            'email' => 'updated.operator@example.test',
            'phone' => '9876500001',
            'address' => 'Station Road, Ichapur',
        ]);
    }

    public function test_login_redirects_to_first_permitted_sidebar_module(): void
    {
        $user = User::factory()->create(['email' => 'inventory@example.test', 'password' => 'password']);
        $branch = Branch::create(['code' => 'ICH-01', 'name' => 'Ichapur Main Branch']);
        $role = Role::create(['name' => 'Inventory Only']);
        $permission = Permission::create(['module' => 'Inventory', 'action' => 'View']);
        $role->permissions()->attach($permission);

        Employee::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'branch_id' => $branch->id,
            'employee_code' => 'EMP-998',
            'name' => 'Inventory Staff',
            'phone' => '9999999998',
            'status' => 'active',
        ]);

        $this->post('/login', ['email' => 'inventory@example.test', 'password' => 'password'])
            ->assertRedirect(route('inventory'));
    }

    public function test_start_redirects_to_first_permitted_menu_when_dashboard_is_not_allowed(): void
    {
        $this->actingAsEmployeeWithPermissions([['POS', 'View']]);

        $this->get('/start')->assertRedirect(route('pos'));
    }

    public function test_logout_link_request_logs_out_without_csrf_error(): void
    {
        $this->actingAsEmployeeWithPermissions([['POS', 'View']]);

        $this->get('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }
}
