<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      if (request()->ajax())
      {
        $model = Account::where('user_id', auth()->user()->id);
        return DataTables::of($model)
        ->order(function ($query) {
          if (!request()->has('updated_at')) {
              $query->orderBy('updated_at', 'desc');
          }
        })
        ->addIndexColumn()
        ->addColumn('action',function($data){
          $disable = (auth()->user()->status == User::STATUS_ACTIVE) ? '' : 'disabled';
          return '
          <button type="button" '.$disable.' class="btn btn-primary table-edit-button" data-id="'.$data->id.'"><i class="bi bi-pencil-square"></i></i></button>
          <button type="button" '.$disable.' class="btn btn-danger table-delete-button" data-id="'.$data->id.'"><i class="bi bi-trash-fill"></i></button>';
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
        ->rawColumns(['action']) // render as raw html instead of string
        ->toJson();
      }
      return view('account.index');
    }

    public function listAccount()
    {
      if (request()->ajax()) {
          $data = Account::where('user_id', auth()->user()->id)
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
      $validate = Validator::make($request->all(), [
        "name" => 'required',
      ], []);
      if ($validate->fails()) {
          return response()->json(['errors'=>$validate->errors()->all()]);
      }

      Account::create([
        "user_id" => auth()->user()->id, // get user id from auth user
        "name" => $request->input('name'),
      ]);

      return response()->json(['success'=>'success insert']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
     
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
      $data = Account::find($id);
      if (request()->ajax()) { // used when request using ajax
        return response()->json([
            'data' => $data,
        ]);
      }

      return;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
      $validate = Validator::make($request->all(), [
        "name" => 'required',
      ], []);
      if ($validate->fails()) {
          return response()->json(['errors'=>$validate->errors()->all()]);
      }

      $data = Account::find($id);
      $data->name = $request->input('name');
      $data->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
      Account::destroy($id);
    }
}
