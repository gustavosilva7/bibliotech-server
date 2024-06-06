<?php

namespace App\Http\Controllers;

use App\Models\StudentsProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentsController extends Controller
{
    public function index()
    {
        $students = StudentsProfile::with('user')->get();

        return response()->json(['students' => $students], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'serie' => 'required|integer',
            'class' => 'required|integer',
        ]);

        $credentials = $request->only('email', 'password');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 2
        ]);

        $student = StudentsProfile::create([
            'user_id' => $user->id,
            'serie' => $request->serie,
            'class' => $request->class
        ]);

        if (auth()->attempt($credentials)) {
            return response()->json(['student' => $student], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function show($id)
    {
        $student = StudentsProfile::with('user')->find($id);

        return response()->json(['student' => $student], 200);
    }
}
