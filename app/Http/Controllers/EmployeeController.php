<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Employee\GetEmployeeRequest;
use App\Http\Requests\Employee\CreateEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Manager;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees
     */
    public function index(GetEmployeeRequest $request, Employee $employee): JsonResponse
    {
        try {
            if ($response = $this->authorizeEmployee($employee, 'read.employee')) {
                return $response;
            }

            $employee = Employee::query();

            if($request->filled('name')){
                $employee = $employee->where('name', 'like', '%'.$request->name.'%');
            }
            if($request->filled('sort')){
                $employee = $employee->orderBy('name', $request->sort);
            }

            $employee = $employee->paginate(10);
            
            return $this->jsonResponse('Employees retrieved successfully', $employee->toArray());
        } catch (\Exception $e) {
            Log::error('Error retrieving employees: ' . $e->getMessage());
            return $this->jsonResponse('Error retrieving employees', [], 500);
        }
    }

    /**
     * Display the specified employee
     */
    public function show(int $id): JsonResponse
    {
        try {

            $employee = Employee::findOrFail($id);

            if ($response = $this->authorizeEmployee($employee, 'read.employee')) {
                return $response;
            }

            return $this->jsonResponse('Employee retrieved successfully', $employee->toArray());
        } catch (\Exception $e) {
            Log::error('Error retrieving employee: ' . $e->getMessage());
            return $this->jsonResponse('employee not found', [], 404);
        }
    }

    /**
     * Create a new employee
     */
    public function store(CreateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        try {

            $data = $request->validated();

            if ($response = $this->authorizeEmployee($employee, 'create.employee')) {
                return $response;
            }

            //get company id from authenticated manager account
            $managerCompanyId = Manager::find(auth()->user()->id)->company_id;

            $mapData = [
                'company_id' => $managerCompanyId,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
            ];

            $employee = Employee::create($mapData);

            return $this->jsonResponse('Employee created successfully', $employee->toArray(), 201);
        } catch (\Exception $e) {
            Log::error('Error creating employee: ' . $e->getMessage());
            return $this->jsonResponse('Error creating employee', [], 500);
        }
    }

    /**
     * Update a employee data
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        try {
            if ($response = $this->authorizeEmployee($employee, 'update.employee')) {
                return $response;
            }

            $employee->update($request->validated());

            return $this->jsonResponse('Employee updated successfully', $employee->toArray());
        } catch (\Exception $e) {
            Log::error('Error updating employee: ' . $e->getMessage());
            return $this->jsonResponse('Error updating employee', [], 500);
        }
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Employee $employee): JsonResponse
    {
        try {
            if ($response = $this->authorizeEmployee($employee, 'delete.employee')) {
                return $response;
            }

            $employee->delete();

            return $this->jsonResponse('Employee deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting employee: ' . $e->getMessage());
            return $this->jsonResponse('Error deleting employee', [], 500);
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
     * Check if user is authorized to access employee data
     * 
     * param Employee $employee, string $permission
     */
    private function authorizeEmployee(Employee $employee, string $permission): ?JsonResponse
    {
        if (!Gate::allows($permission, $employee)) {
            return $this->jsonResponse(
                'You are not authorized to access this resource',
                [],
                403
            );
        }
        return null;
    }
}
