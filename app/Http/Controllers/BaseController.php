<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as LaravelBaseController;
use Inertia\Inertia;

class BaseController extends LaravelBaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    /**
     * Current authenticated user with common relations
     */
    protected function currentUser()
    {
        return auth()->user()->loadMissing([
            'companyOwner.company',
            'roles.permissions',
            'permissions'
        ]);
    }
    
    /**
     * Check if user has super-admin role
     */
    protected function isSuperAdmin()
    {
        return auth()->user()->hasRole('super-admin');
    }
    
    /**
     * Get user's company (if any)
     */
    protected function userCompany()
    {
        return auth()->user()->companyOwner?->company;
    }
    
    /**
     * Success JSON response
     */
    protected function apiSuccess($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }
    
    /**
     * Error JSON response
     */
    protected function apiError(string $message = 'Error', int $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }
    
    /**
     * Inertia render with common props
     */
    protected function inertiaRender(string $component, array $props = [])
    {
        $user = auth()->user();
        
        // Common props for all Inertia pages
        $commonProps = [
            'auth' => $user ? [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'company' => $user->companyOwner?->company,
                ]
            ] : null,
            'flash' => session()->get('flash', []),
        ];
        
        return Inertia::render($component, array_merge($commonProps, $props));
    }
    
    /**
     * Pagination helper
     */
    protected function paginate($query, Request $request, int $perPage = 15)
    {
        return $query->paginate(
            $request->get('per_page', $perPage)
        )->withQueryString();
    }
    
    /**
     * Validate with custom rules
     */
    protected function validateRequest(Request $request, array $rules, array $messages = [])
    {
        return $request->validate($rules, $messages);
    }
    
    /**
     * Handle try-catch for API methods
     */
    protected function handleApiCall(callable $callback, string $successMessage = 'Operation successful')
    {
        try {
            $result = $callback();
            return $this->apiSuccess($result, $successMessage);
        } catch (\Exception $e) {
            \Log::error('API Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->apiError(
                env('APP_DEBUG') ? $e->getMessage() : 'Something went wrong',
                500
            );
        }
    }
}