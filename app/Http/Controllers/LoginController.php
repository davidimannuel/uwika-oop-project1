<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        return view('login.index');
    }

    public function authenticate(Request $request)
    {
      $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
      ]); 

      $remember = $request->has('remember');
      if (Auth::attempt($credentials,$remember)) {
        $request->session()->regenerate();
        
        return redirect()->route('home');
      }

      return back()->with(['loginError' => 'invalid username / password']);
    }

    public function logout(Request $request) 
    {
        Auth::logout();
    
        $request->session()->invalidate();
    
        $request->session()->regenerateToken();
    
        return redirect()->route('login');
    }
}
