<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display the form to add a new user.
     *
     * @return \Illuminate\View\View
     */
    public function add()
    {
        return view('content.users.add-users');
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:receptionist,admin',
        ]);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        if ($user->save()) {
            return redirect()->route('Users-list')->with('success', 'User created successfully');
        }

        return redirect()->route('Users-list')->with('error', 'User creation failed');
    }

    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\View\View
     */
    public function list()
    {
        $users = User::where('id', '!=', Auth::user()->id)->get();

        return view('content.users.list-users', compact('users'));
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('Users-list')->with('success', 'User deleted successfully');
    }
}
