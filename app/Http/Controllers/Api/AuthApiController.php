<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JsonResponseTrait;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TokenResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    use JsonResponseTrait;

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name'=>'required|max:55',
            'email'=>'email|required|unique:users',
            'password'=>'required|confirmed'
        ]);
        $validatedData['password'] = bcrypt($request->password);
        $user = User::create($validatedData);
        $accessToken = $user->createToken('authToken')->accessToken;
        return $this->json('User created successfully',['user'=> $user, 'access_token'=> $accessToken]);
    }
    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if(!auth()->attempt($loginData)) {
            return $this->bad('Invalid credentials', Response::HTTP_UNAUTHORIZED);
        }
        $user= auth()->user();
        return $this->json('login successfully',[
            'token' => new TokenResource($user->createToken('authToken')),
            'user' => new UserResource($user),
        ]);
    }
    public function logout()
    {
        if (Auth::check()) {
            $user = Auth::user()->token();
            $user->revoke();
            return $this->json('Logout successfully');
        }
        return $this->bad('You are not authenticate yet',Response::HTTP_UNAUTHORIZED);
    }
}
