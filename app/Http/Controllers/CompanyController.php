<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Company\GetCompanyRequest;
use App\Http\Requests\Company\CreateCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use App\Models\Role;

class CompanyController extends Controller
{
    // Define default values as class constants
    private const DEFAULT_PASSWORD = 'password';

    /**
     * Display a listing of companies
     */
    public function index(GetCompanyRequest $request, Company $company): JsonResponse
    {
        try {
            if ($response = $this->authorizeCompany($company, 'read.company')) {
                return $response;
            }

            $companies = Company::query()
                ->when($request->filled('name'), function ($query) use ($request) {
                    return $query->where('name', 'like', '%' . $request->name . '%');
                })
                ->when($request->filled('sort'), function ($query) use ($request) {
                    return $query->orderBy('name', $request->sort);
                })
                ->paginate(10);
            
            return $this->jsonResponse('Companies retrieved successfully', $companies->toArray());
        } catch (\Exception $e) {
            Log::error('Error retrieving companies: ' . $e->getMessage());
            return $this->jsonResponse('Error retrieving companies', [], 500);
        }
    }

    /**
     * Display the specified company
     */
    public function show(int $id): JsonResponse
    {
        try {
            $company = Company::findOrFail($id);
            
            if ($response = $this->authorizeCompany($company, 'read.company')) {
                return $response;
            }

            return $this->jsonResponse('Company retrieved successfully', $company->toArray());
        } catch (\Exception $e) {
            Log::error('Error retrieving company: ' . $e->getMessage());
            return $this->jsonResponse('Company not found', [], 404);
        }
    }

    /**
     * Create a new company
     * 
     * You can use the data response for login to access the manager account for this company
     * 
     */
    public function store(CreateCompanyRequest $request, Company $company): JsonResponse
    {
        try {

            if ($response = $this->authorizeCompany($company, 'create.company')) {
                return $response;
            }

            $validatedData = $request->validated();

            $result =  DB::transaction(function () use ($validatedData) {
                $company = Company::create($validatedData);
                
                $managerRole = Role::where('name', 'manager')->firstOrFail();
                
                $company->roles()->attach($managerRole);

                // Create manager account with cleaner attribute handling
                $user = User::create([
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'password' => Hash::make(self::DEFAULT_PASSWORD),
                    'role_id' => $managerRole->id
                ]);
                
                // Create manager using relationship
                $manager = $company->managers()->create([
                    'name' => $validatedData['name'],
                    'phone' => $validatedData['phone'],
                    'user_id' => $user->id
                ]);

                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => self::DEFAULT_PASSWORD,
                ];
            });

            return $this->jsonResponse('Company created successfully', $result, 201);
        } catch (\Exception $e) {
            Log::error('Error creating company: ' . $e->getMessage());
            return $this->jsonResponse('Error creating company', [], 500);
        }
    }

    /**
     * Update the specified company
     * 
     */
    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        try {

            if ($response = $this->authorizeCompany($company, 'update.company')) {
                return $response;
            }

            $validatedData = $request->validated();
            
            $result = DB::transaction(function () use ($validatedData, $company, $request) {
                
                $company->update($request->validated());

                return $company;
            });

            return $this->jsonResponse('Company updated successfully', $result->toArray(), 200);
        } catch (\Exception $e) {
            Log::error('Error updating company: ' . $e->getMessage());
            return $this->jsonResponse('Error updating company', [], 500);
        }
    }

    /**
     * Remove the specified company
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $company = Company::findOrFail($id);
            
            if ($response = $this->authorizeCompany($company, 'delete.company')) {
                return $response;
            }

            $company->delete();
            return $this->jsonResponse('Company deleted successfully', [], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting company: ' . $e->getMessage());
            return $this->jsonResponse('Error deleting company', [], 500);
        }
    }

    /**
     * Create standardized JSON response
     * 
     * params string $message, array $data, int $statusCode
     */
    private function jsonResponse(string $message, array $data = [], int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status_code' => $statusCode,
            'status_message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Check if user is authorized to access company
     * 
     * param Company $company, string $permission
     */
    private function authorizeCompany(Company $company, string $permission): ?JsonResponse
    {
        if (!Gate::allows($permission, $company)) {
            return $this->jsonResponse(
                'You are not authorized to access this resource',
                [],
                403
            );
        }
        return null;
    }
}