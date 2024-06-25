<?php

namespace App\Http\Controllers;

use App\Models\StudentsProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $applicationType = $request->input('applicationType');

        if (auth()->attempt($credentials)) {
            $user = auth()->user();

            if (($user->role_id == 1 && $applicationType == 2) || ($user->role_id != 1 && $applicationType == 1)) {
                return response()->json(['message' => 'Este usuário não possui autorização para esta aplicação'], 401);
            }

            $token = $user->createToken('authToken')->plainTextToken;
            return response()->json(['token' => $token], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'serie' => 'required|integer',
            'class' => 'required|integer',
        ]);

        $credentials = $request->only('email', 'password');
        $applicationType = $request->input('applicationType');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 2
        ]);

        StudentsProfile::create([
            'user_id' => $user->id,
            'serie' => $request->serie,
            'class' => $request->class
        ]);

        if (auth()->attempt($credentials)) {
            $user = auth()->user();

            if (($user->role_id == 1 && $applicationType == 2) || ($user->role_id != 1 && $applicationType == 1)) {
                return response()->json(['message' => 'Este usuário não possui autorização para esta aplicação'], 401);
            }

            $token = $user->createToken('authToken')->plainTextToken;
            return response()->json(['token' => $token], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function user()
    {
        $auth = auth()->user();

        $user = User::with(['studentProfile', 'role'])->find($auth->id);

        return response()->json($user);
    }

    public function addImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $user = auth()->user();

        if ($request->hasFile('image')) {
            $uploadFolder = 'users';
            $image = $request->file('image');
            $image_uploaded_path = $image->store($uploadFolder, 's3');
            $uploadedImageResponse = array(
                "image_name" => basename($image_uploaded_path),
                "image_url" => Storage::disk('s3')->url($image_uploaded_path),
                "mime" => $image->getClientMimeType()
            );

            $path = $uploadedImageResponse['image_url'];
        }

        $user->image = $path;
        $user->save();

        return response()->json(['message' => 'Image uploaded successfully'], 200);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
    }
}
