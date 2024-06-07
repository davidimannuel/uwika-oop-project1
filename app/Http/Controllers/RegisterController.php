<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function index()
    {
        return view('register.index');
    }

    public function store(Request $request)
    {
      $validated_input = $request->validate([
          'name' => ['required'],
          'email' => ['required', 'email:dns', 'unique:users'],
          'password' => ['required', 'min:5'],
          'password_confirmation' => ['required', 'min:5', 'same:password'],
      ]);
      
      // encrypt password
      $validated_input['password'] = Hash::make($validated_input['password']);
      // set status
      $validated_input['status'] = User::STATUS_INACTIVE;
      $user = User::create($validated_input);

      $request->session()->flash('register_success', 'Registration successful');

      return redirect()->route('login');
    }
}
