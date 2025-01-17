<?php

namespace Tests\Unit\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Manager;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;

class ManagerControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $manager;
    protected $employee;

    public function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::insert([
            ['id' => 1, 'name' => 'super_admin'],
            ['id' => 2, 'name' => 'manager'],
            ['id' => 3, 'name' => 'employee']
        ]);

        // Create users with different roles
        $this->superAdmin = User::factory()->create(['role_id' => '1']);
        $this->manager = User::factory()->create(['role_id' => '2']);
        $this->employee = User::factory()->create(['role_id' => '3']);

        // Create company first
        $this->company = Company::factory()->create();
        
        $this->company->managers()->create([
            'company_id' => $this->company->id,
            'name' => $this->manager->name,
            'phone' => '1234567890',
            'address' => 'Jl. Manager No. 1'
        ]);

        // Define gate abilities
        Gate::define('read.manager', function ($user) {
            return in_array($user->role->name, ['manager']);
        });

        Gate::define('update.manager', function ($user) {
            return in_array($user->role->name, [ 'manager']);
        });

        Gate::define('delete.manager', function ($user) {
            return $user->role->name === 'super_admin';
        });

    }

    #[Test]
    public function super_admin_can_list_managers()
    {
        $this->actingAs($this->superAdmin, 'api');

        $response = $this->getJson('/api/manager');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status_code',
                    'status_message',
                    'data' => [
                        'current_page',
                        'data',
                        'first_page_url',
                        'from',
                        'last_page',
                        'last_page_url',
                        'links',
                        'next_page_url',
                        'path',
                        'per_page',
                        'prev_page_url',
                        'to',
                        'total',
                    ]
                ]);
    }

    #[Test]
    public function manager_can_list_managers()
    {
        $this->actingAs($this->manager, 'api');

        $response = $this->getJson('/api/manager');
        
        $response->assertStatus(200);
    }

    #[Test]
    public function employee_cannot_access_managers()
    {
        $this->actingAs($this->employee, 'api');

        Gate::shouldReceive('allows')
            ->with('read.manager', $this->manager)
            ->andReturn(false);

        $response = $this->getJson('/api/manager');
        
        $response->assertStatus(403);
    }

    #[Test]
    public function super_admin_can_update_manager()
    {
        $this->actingAs($this->superAdmin, 'api');

        $updatedData = ['name' => $this->faker->name];

        $response = $this->putJson("/api/manager/{$this->manager->id}", $updatedData);

        $response->assertStatus(200);
    }

    #[Test]
    public function manager_can_update_manager()
    {
        $this->actingAs($this->manager, 'api');

        $updatedData = ['name' => $this->faker->name];

        $response = $this->putJson("/api/manager/{$this->manager->id}", $updatedData);

        $response->assertStatus(200);
    }

    #[Test]
    public function employee_cannot_update_manager()
    {
        $this->actingAs($this->employee, 'api');

        $updatedData = ['name' => $this->faker->name];

        $response = $this->putJson("/api/manager/{$this->manager->id}", $updatedData);

        $response->assertStatus(403);
    }

    #[Test]
    public function super_admin_can_delete_manager()
    {
        $this->actingAs($this->superAdmin, 'api');

        $response = $this->deleteJson("/api/manager/{$this->manager->id}");

        $response->assertStatus(200)
                ->assertJsonFragment(['status_message' => 'Manager deleted successfully']);

        $this->assertDatabaseMissing('managers', ['id' => $this->manager->id]);
    }

    #[Test]
    public function manager_cannot_delete_manager()
    {
        $this->actingAs($this->manager, 'api');

        $response = $this->deleteJson("/api/manager/{$this->manager->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function employee_cannot_delete_manager()
    {
        $this->actingAs($this->employee, 'api');

        $response = $this->deleteJson("/api/manager/{$this->manager->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function it_returns_404_for_non_existent_manager()
    {
        $this->actingAs($this->superAdmin, 'api');
        
        $nonExistentId = 99999;
        
        $response = $this->getJson("/api/manager/{$nonExistentId}");

        $response->assertStatus(404)
                ->assertJsonFragment([
                    'status_message' => 'manager not found'
                ]);
    }

    #[Test]
    public function it_validates_update_request()
    {
        $this->actingAs($this->superAdmin, 'api');

        $response = $this->putJson("/api/manager/{$this->manager->id}", [
            'name' => '' // Invalid empty name
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function unauthenticated_users_cannot_access_api()
    {
        $response = $this->getJson('/api/manager');
        
        $response->assertStatus(401);
    }
}