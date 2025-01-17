<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Manager;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $managerUser;
    private User $employeeUser;
    private Manager $manager;
    private Employee $employee;
    private Company $company;
    private Role $managerRole;
    private Role $employeeRole;
    private string $managerToken;
    private string $employeeToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $this->managerRole = Role::create([
            'id' => 2,
            'name' => 'manager',
            'display_name' => 'Manager',
            'description' => 'Manager role'
        ]);

        $this->employeeRole = Role::create([
            'id' => 3,
            'name' => 'employee',
            'display_name' => 'Employee',
            'description' => 'Employee role'
        ]);

        // Create company
        $this->company = Company::create([
            'name' => 'Test Company',
            'address' => 'Test Address',
            'email' => 'test@mail.com',
            'phone' => '1234567890'
        ]);
        
        // Create manager user
        $this->managerUser = User::factory()->create([
            'role_id' => $this->managerRole->id,
            'email' => 'manager@test.com',
            'password' => Hash::make('password')
        ]);
        
        // Create manager
        $this->manager = Manager::create([
            'user_id' => $this->managerUser->id,
            'company_id' => $this->company->id,
            'name' => 'Test Manager',
            'phone' => '1234567890',
            'address' => 'Test Address'
        ]);

        // Create employee
        $this->employee = Employee::create([
            'company_id' => $this->company->id,
            'name' => 'Test Employee',
            'phone' => '0987654321',
            'address' => 'Employee Address'
        ]);

        // Generate JWT tokens
        $this->managerToken = JWTAuth::fromUser($this->managerUser);
    }

    /**
     * Test index method
     */
    public function test_manager_can_get_all_employees()
    {
        $this->mock('Illuminate\Support\Facades\Gate')
            ->shouldReceive('allows')
            ->with('read.employee', \Mockery::any())
            ->andReturn(true);

        $headers = ['Authorization' => 'Bearer ' . $this->managerToken];
        
        $response = $this->json('GET', '/api/employee', [], $headers);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status_code',
                    'status_message',
                    'data' => [
                        'current_page',
                        'data',
                        'total'
                    ]
                ]);
    }

    /**
     * Test store method
     */
    public function test_manager_can_create_employee()
    {
        $this->mock('Illuminate\Support\Facades\Gate')
            ->shouldReceive('allows')
            ->with('create.employee', \Mockery::any())
            ->andReturn(true);

        $employeeData = [
            'name' => 'New Employee',
            'phone' => '1231231234',
            'address' => 'New Address'
        ];

        $headers = ['Authorization' => 'Bearer ' . $this->managerToken];
        
        $response = $this->json('POST', '/api/employee', $employeeData, $headers);

        $response->assertStatus(201)
                ->assertJsonFragment($employeeData);
        
        $this->assertDatabaseHas('employees', [
            'name' => 'New Employee',
            'company_id' => $this->company->id
        ]);
    }
}