<?php

namespace App\Http\Controllers;

use App\Product;
use App\Sale;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\User;
use Auth;



class AdminController extends Controller
{
    public function getDashboard(){
        $today=date("Y-m-d");
        $users=User::get();
        $items=Product::get();
        $sales=Sale::where('created_at', "LIKE", "%$today%")->get();
        return view ('admin.dashboard.home')->with(['users'=>$users, 'items'=>$items, 'sales'=>$sales]);
    }
    public function getError(){
        return view ('admin.dashboard.error');
    }
    public function getUsers(){
        $users=User::all();
        $roles=Role::all();
        return view ('admin.users.users')->with(['users'=>$users])->with(['roles'=>$roles]);
    }
    public function getNewUser(){
        $roles=Role::all();
        return view ('admin.users.new-user')->with(['roles'=>$roles]);
    }
    public function postNewUser(Request $request){
        $this->validate($request,[
           'name'=>'required|unique:users',
           'full_name'=>'required',
           'email'=>'required|email|unique:users',
           'role'=>'required',
           'password'=>'required|min:5'
        ]);

        $user=new User();
        $user->name=$request['name'];
        $user->full_name=$request['full_name'];
        $user->email=$request['email'];
        $user->password=bcrypt($request['password']);
        $user->save();
        $user->syncRoles($request['role']);
        return redirect()->back()->with('info', 'The new user account have been created.');
    }
    public function postDeleteUser(Request $request){
        $id=$request['id'];
        $user=User::where('id', $id)->first();
        $user->delete();
        return redirect()->back()->with('info', "The selected user account have been deleted.");
    }
    public function postUpdateUser(Request $request){
        $name=$request['name'];
        $full_name=$request['full_name'];
        $id=$request['id'];
        $email=$request['email'];
        $role=$request['role'];
        $password=$request['password'];

        $user=User::where('id', $id)->first();

        if($password){
            $user->password=bcrypt($password);
        }
        $user->name=$name;
        $user->full_name=$full_name;
        $user->email=$email;
        $user->update();
        $user->syncRoles($role);
        return redirect()->back()->with('info','The selected user account have been updated.');
    }
    public function getUserAccountSetting(){
        return view ('admin.users.setting');
    }
    public function postUpdatePassword(Request $request){
        $this->validate($request,[
           'new_password'=>'required',
           'new_password_again'=>'required|same:new_password'
        ]);

        $user=User::where('id', Auth::User()->id)->first();
        $user->password=bcrypt($request['new_password']);
        $user->update();
        return redirect()->back()->with('info', 'Your account password have been updated.');
    }
}
