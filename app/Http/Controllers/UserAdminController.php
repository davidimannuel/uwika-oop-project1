<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class UserAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(request()->ajax())
        {
          $model = User::where('is_admin', false);
          return DataTables::of($model)
          ->order(function ($query) {
            if (!request()->has('updated_at')) {
                $query->orderBy('updated_at', 'desc');
            }
          })
          ->addIndexColumn()
          ->addColumn('status_action',function($data){
            $html = '';
            if ($data->status == User::STATUS_ACTIVE) {
              $html .= '<button type="button" class="btn btn-success table-status-button" data-id="'.$data->id.'">Active</button>';
            } else {
              $html .= '<button type="button" class="btn btn-danger table-status-button" data-id="'.$data->id.'">Inactive</button>';
            }
            return $html;
          })
          ->editColumn('created_at',function($data){
            $carbonTime = Carbon::parse($data->created_at, 'Asia/Jakarta');
            return $carbonTime->format('Y-m-d H:i:s');
          })
          ->editColumn('updated_at',function($data){
            $carbonTime = Carbon::parse($data->updated_at, 'Asia/Jakarta');
            return $carbonTime->format('Y-m-d H:i:s');
          })
          ->addColumn('action',function($data){
            return '<a href="'.route('user.admin.edit',$data->id).'" class="btn btn-primary" data-id="'.$data->id.'"><i class="bi bi-pencil-square"></i></i></a>';
          })
          ->setRowClass('text-center')
          ->rawColumns(['status_action','action']) // render as raw html instead of string
          ->toJson();
        }
        return view('user.admin.index');
    }

    public function listUser()
    {
      if (request()->ajax()) {
          $data = User::where('is_admin', false)
          ->where('name','ILIKE',"%".request('search')."%")->get();
          return response()->json([
            'data' => $data,
            'request' => request('search'),
          ]);
      }
        
      return;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
      $user = User::where('id', $id)
          ->where('is_admin', false)
          ->first();
        
      return view('user.admin.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
      $user = User::where('id', $id)
      ->where('is_admin', false)
      ->first();

      $validate = [
        'name' => ['required'],
        'email' => ['required', 'email:dns', 'unique:users,email,'.$user->id],
      ];
      // if password is not empty, validate password adn password confirmation
      if ($request->password) {
        $validate['password'] = ['required', 'min:5'];
        $validate['password_confirmation'] = ['required', 'same:password'];
      }
      $validated_input = $request->validate($validate);
      $user->name = $validated_input['name'];
      $user->email = $validated_input['email'];
      if ($request->password) {
        $user->password = Hash::make($validated_input['password']);
      }
      $user->save();
      
      $request->session()->flash('update_success', 'Update successful');

      return redirect()->route('user.admin.index');
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function patch_status(Request $request, string $id)
    {
      $user = User::where('id', $id)
        ->where('is_admin', false)
        ->first();
      if ($user->status == 'active') {
        $user->status = User::STATUS_INACTIVE;
      } else {
        $user->status = User::STATUS_ACTIVE;
      }
      $user->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
