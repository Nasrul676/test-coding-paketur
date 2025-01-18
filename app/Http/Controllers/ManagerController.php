<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Manager\GetManagerRequest;
use App\Http\Requests\Manager\CreateManagerRequest;
use App\Http\Requests\Manager\UpdateManagerRequest;
use App\Models\Manager;

class ManagerController
{
    /**
     * Display all of managers or searched managers
     */
    public function index(GetManagerRequest $request, Manager $manager): JsonResponse
    {
        try {
            if ($response = $this->authorizeManager($manager, 'read.manager')) {
                return $response;
            }

            $manager = Manager::query()
                ->when($request->filled('name'), function ($query) use ($request) {
                    return $query->where('name', 'like', '%' . $request->name . '%');
                })
                ->when($request->filled('sort'), function ($query) use ($request) {
                    return $query->orderBy('name', $request->sort);
                })
                ->paginate(10);
            
            return $this->jsonResponse('Managers retrieved successfully', $manager->toArray());
        } catch (\Exception $e) {
            Log::error('Error retrieving managers: ' . $e->getMessage());
            return $this->jsonResponse('Error retrieving managers', [], 500);
        }
    }

    /**
     * Display the specified manager
     */
    public function show(int $id): JsonResponse
    {
        try {

            $manager = Manager::findOrFail($id);
            
            if ($response = $this->authorizeManager($manager, 'read.manager')) {
                return $response;
            }

            return $this->jsonResponse('Manager retrieved successfully', $manager->toArray());
        } catch (\Exception $e) {
            Log::error('Error retrieving manager: ' . $e->getMessage());
            return $this->jsonResponse('manager not found', [], 404);
        }
    }

    /**
     * Update a manager data
     */
    public function update(UpdateManagerRequest $request, Manager $manager): JsonResponse
    {
        try {
            if ($response = $this->authorizeManager($manager, 'update.manager')) {
                return $response;
            }

            $manager->update($request->validated());

            return $this->jsonResponse('Manager updated successfully', $manager->toArray());
        } catch (\Exception $e) {
            Log::error('Error updating manager: ' . $e->getMessage());
            return $this->jsonResponse('Error updating manager', [], 500);
        }
    }

    /**
     * Remove the specified manager
     * 
     * Only super admin can delete a manager
     */
    public function destroy(Manager $manager): JsonResponse
    {
        try {
            if ($response = $this->authorizeManager($manager, 'delete.manager')) {
                return $response;
            }

            $manager->delete();

            return $this->jsonResponse('Manager deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting manager: ' . $e->getMessage());
            return $this->jsonResponse('Error deleting manager', [], 500);
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
     * Check if user is authorized to access managers data
     * 
     * param Manager $manager, string $permission
     */
    private function authorizeManager(Manager $manager, string $permission): ?JsonResponse
    {
        Log::alert($permission);
        if (!Gate::allows($permission, $manager)) {
            return $this->jsonResponse(
                'You are not authorized to access this resource',
                [],
                403
            );
        }
        return null;
    }
}
