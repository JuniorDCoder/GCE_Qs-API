<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request){
        try {
                $validator = Validator::make($request->all(),
            [
                'name' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['min:8'],
                'school' => ['required',],
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'error' => $validator->errors(),
                    ], 401
                );
            }

            try {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                    'school' => $request->school,
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Registration successful',
                    'user' => $user,
                    'token' => $user->createToken("API TOKEN")->plainTextToken
                ], 201);
            } catch (QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Email already exists.'
                    ], 422);
                }
                throw $e;
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'error' => $validator->errors(),
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email and Password do not match our records',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();
            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'user' => $user,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
    public function editProfile(Request $request){}
}
