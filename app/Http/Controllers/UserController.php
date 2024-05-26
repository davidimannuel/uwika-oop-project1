<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
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
            $disable = (auth()->user()->is_admin) ? '' : 'disabled';
            $html = '';
            if ($data->status == User::STATUS_ACTIVE) {
              $html .= '<button type="button" '.$disable.' class="btn btn-success table-status-button" data-id="'.$data->id.'">Active</button>';
            } else {
              $html .= '<button type="button" '.$disable.' class="btn btn-danger table-status-button" data-id="'.$data->id.'">Inactive</button>';
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
          ->setRowClass('text-center')
          ->rawColumns(['status_action']) // render as raw html instead of string
          ->toJson();
        }
        return view('user.index');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
