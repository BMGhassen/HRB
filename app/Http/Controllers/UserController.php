<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //afficher la liste des articles
    public function index(){
        $users = User::with('client')->get();
        return view('admin.users.users')->with('users',$users);
    }

    public function destroy($id){
        $user = User::find($id);
        if($user->delete()){
            return redirect()->back();
        }else{
            echo "error";
        }
    }

    public function update(Request $request){

        $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $request->id_user,
        'role' => 'required|string|in:client,vendeur,admin',
        'state' => 'string|max:255',
        'code' => 'nullable|string|max:50|unique:users,code_client,' . $request->id_user,
        ]);
        $id = $request->id_user;

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->state = $request->has('state') ? 'Actif' : 'NonActif';
        $user->code_client = $request->filled('code') ? $request->code : null;
        if($user->save()){
            return redirect()->route('Users')->with('success', 'User updated successfully.');
        }
        else {
            return back()->with('error', 'Failed to update user.');
        }
    }
}
