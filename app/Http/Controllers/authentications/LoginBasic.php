<?php

namespace App\Http\Controllers\authentications;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class LoginBasic extends Controller
{


  public function login(Request $request)
  {
    if ($request->session()->has('logout')) {
      $request->session()->forget('logout');
      return response()->view('auth.login')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
    // validate the input 
    $request->validate([
      'email' => 'required|email',
      'password' => 'required'
    ]);

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {

      return redirect()->route('dashboard');
    }

    return back()->withErrors([
      'email' => 'Invalid credentials'
    ]);
  }

  public function logout(Request $request)
  {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
  }

  public function __construct()
  {
    $this->middleware('auth')->except('login');
  }
}
