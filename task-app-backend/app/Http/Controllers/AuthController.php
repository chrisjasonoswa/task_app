<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Log;
use App\Http\Requests\LoginRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\ApiResponseResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Repositories\UserRepositoryInterface;

class AuthController extends Controller
{
    protected $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function login(LoginRequest $request)
    {
        try{
            $validated = $request->validated();
            $user = $this->userRepo->findByEmail($validated['email']);

            if (!$user ||!Hash::check($validated['password'], $user->password)) {
                return ApiResponseResource::make([
                    'success' => false,
                    'message' => 'Failed to log in. The provided credentials are incorrect.',
                ])->response()->setStatusCode(401);
            }
            $token = $user->createToken($user->name)->plainTextToken;
            return ApiResponseResource::make([
                'success' => true,
                'message' => 'You have successfully logged in',
                'data' => [
                    'user' => UserResource::make($user),
                    'token' => $token
                ]
            ])->response();

        }
        catch(ModelNotFoundException $e){
            Log::error('An error occured while fetching the tasks:' . $e->getMessage());
            return ApiResponseResource::make([
                'success' => false,
                'message' => 'Failed to log in. The provided credentials are incorrect.',
            ])->response()->setStatusCode(code: 404);
        }
        catch(\Exception $e){
            Log::error('An error occured while fetching the tasks:' . $e->getMessage());
        }
    }

    public function testLogin(Request $request)
    {
       return 'API';
    }
}
