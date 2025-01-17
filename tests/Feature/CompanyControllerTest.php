<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use PHPUnit\Framework\Attributes\Test;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $manager;
    protected $employee;
    protected $token;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $roles = Role::insert([
            ['id' => 1, 'name' => 'super_admin'],
            ['id' => 2, 'name' => 'manager'],
            ['id' => 3, 'name' => 'employee']
        ]);

        $this->superAdmin = User::factory()->create([
            'role_id' => 1
        ]);
        
        // Create company first
        $this->company = Company::factory()->create();
        
        // Create manager dan associate dengan company melalui table managers
        $this->manager = User::factory()->create([
            'role_id' => 2
        ]);
        
        $this->company->managers()->create([
            'company_id' => $this->company->id,
            'name' => $this->manager->name,
            'phone' => '1234567890',
            'address' => 'Jl. Manager No. 1'
        ]);
        
        $this->employee = User::factory()->create([
            'role_id' => 3
        ]);

        // Define Gates for testing
        Gate::define('read.company', function (User $user, Company $company) {
            return in_array($user->role_id, [1, 2]); // super_admin or manager
        });

        Gate::define('create.company', function (User $user, Company $company) {
            return $user->role_id === 1; // only super_admin
        });

        Gate::define('update.company', function (User $user, Company $company) {
            return $user->role_id === 1; // only super_admin
        });

        Gate::define('delete.company', function (User $user, Company $company) {
            return $user->role_id === 1; // only super_admin
        });
    }

    private function actingAsSuperAdmin()
    {
        $this->token = JWTAuth::fromUser($this->superAdmin);
        return $this->withHeaders(['Authorization' => 'Bearer ' . $this->token]);
    }

    private function actingAsManager()
    {
        $this->token = JWTAuth::fromUser($this->manager);
        return $this->withHeaders(['Authorization' => 'Bearer ' . $this->token]);
    }

    #[Test]
    public function super_admin_can_view_all_companies()
    {
        Company::factory()->count(3)->create();

        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/company');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status_code',
                'status_message',
                'data' => [
                    'data',
                    'current_page',
                    'per_page'
                ]
            ]);
    }

    #[Test]
    public function manager_can_view_companies()
    {
        // The manager should be able to see at least their own company
        $response = $this->actingAsManager()
            ->getJson('/api/company');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status_code',
                'status_message',
                'data' => [
                    'data',
                    'current_page',
                    'per_page'
                ]
            ]);
    }

    #[Test]
    public function manager_cannot_create_company()
    {
        $companyData = [
            'name' => 'New Company',
            'email' => 'manager@company.com',
            'phone' => '1234567890'
        ];

        $response = $this->actingAsManager()
            ->postJson('/api/company', $companyData);

        $response->assertStatus(403);
    }

    #[Test]
    public function can_search_companies_by_name()
    {
        $company = Company::factory()->create(['name' => 'Test Company ABC']);
        Company::factory()->create(['name' => 'Other Company']);

        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/company?name=ABC');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test Company ABC']);
    }

    #[Test]
    public function can_sort_companies()
    {
        Company::factory()->create(['name' => 'Company B']);
        Company::factory()->create(['name' => 'Company A']);

        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/company?sort=asc');

        $response->assertStatus(200)
            ->assertSeeInOrder(['Company A', 'Company B']);
    }

    #[Test]
    public function super_admin_can_create_company()
    {
        $companyData = [
            'name' => 'New Company',
            'email' => 'manager@company.com',
            'phone' => '1234567890'
        ];

        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/company', $companyData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status_code',
                'status_message',
                'data' => [
                    'name',
                    'email',
                    'password'
                ]
            ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'New Company',
            'phone' => '1234567890'
        ]);
    }

    #[Test]
    public function super_admin_can_update_company()
    {
        $company = Company::factory()->create();
        $updateData = [
            'name' => 'Updated Company',
            'email' => 'updated@email.com',
            'phone' => '9876543210'
        ];

        $response = $this->actingAsSuperAdmin()
            ->putJson("/api/company/{$company->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Updated Company'
        ]);
    }

    #[Test]
    public function manager_cannot_update_company()
    {
        $updateData = [
            'name' => 'Updated Company',
            'email' => 'updated@email.com',
            'phone' => '9876543210'
        ];

        $response = $this->actingAsManager()
            ->putJson("/api/company/{$this->company->id}", $updateData);

        $response->assertStatus(403);
    }

    #[Test]
    public function super_admin_can_delete_company()
    {
        $company = Company::factory()->create();
        
        $response = $this->actingAsSuperAdmin()
            ->deleteJson("/api/company/{$company->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('companies', [
            'id' => $company->id
        ]);
    }

    #[Test]
    public function manager_cannot_delete_company()
    {
        $response = $this->actingAsManager()
            ->deleteJson("/api/company/{$this->company->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function unauthorized_users_cannot_access_company_endpoints()
    {
        $this->token = JWTAuth::fromUser($this->employee);
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->getJson('/api/company');

        $response->assertStatus(403);
    }

    #[Test]
    public function super_admin_can_view_specific_company()
    {
        $company = Company::factory()->create();

        $response = $this->actingAsSuperAdmin()
            ->getJson("/api/company/{$company->id}");
            
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status_code',
                'status_message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'created_at',
                    'updated_at',
                    'deleted_at'
                ]
            ]);
    }

    #[Test]
    public function manager_can_view_specific_company()
    {
        $response = $this->actingAsManager()
            ->getJson("/api/company/{$this->company->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status_code',
                'status_message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'created_at',
                    'updated_at',
                    'deleted_at'
                ]
            ]);
    }
}