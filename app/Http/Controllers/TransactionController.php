<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      $account_id = request("account_id");
      if (request()->ajax())
      {
        $model = Transaction::with('category')->where('account_id', $account_id);
        return DataTables::of($model)
        ->order(function ($query) {
          if (!request()->has('updated_at')) {
              $query->orderBy('updated_at', 'desc');
          }
        })
        ->addIndexColumn()
        ->addColumn('transaction_type',function($data){
          return $data->debit > 0 ? 'income' : 'expense';
        })
        ->addColumn('amount',function($data){
          return $data->debit > 0 ? $data->debit : $data->credit;
        })
        ->addColumn('action',function($data){
          return '
          <button type="button" class="btn btn-primary table-edit-button" data-id="'.$data->id.'"><i class="bi bi-pencil-square"></i></i></button>
          <button type="button" class="btn btn-danger table-delete-button" data-id="'.$data->id.'"><i class="bi bi-trash-fill"></i></button>';
        })
        ->editColumn('transaction_at',function($data){
          $carbonTime = Carbon::parse($data->transaction_at, 'Asia/Jakarta');
          return $carbonTime->format('Y-m-d H:i:s');
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
        ->rawColumns(['poster','action']) // render as raw html instead of string
        ->toJson();
      }
      return view('transaction.index');
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
        "remark" => 'required',
        "account_id" => 'required',
        "category_id" => 'required',
        "transaction_type" => 'required',
        "transaction_at" => 'required',
      ], []);
      if ($validate->fails()) {
          return response()->json(['errors'=>$validate->errors()->all()]);
      }

      $data = [
        "remark" => $request->input('remark'),
        "account_id" => $request->input('account_id'),
        "category_id" => $request->input('category_id'),
        "transaction_at" => $request->input('transaction_at'),
      ];

      if ($request->input('transaction_type') == 'income') {
        $data['debit'] = $request->input('amount');
      } else {
        $data['credit'] = $request->input('amount');
      }

      Transaction::create($data);

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
      $data = Transaction::with('category')->find($id);
      $data['transaction_at_tz'] = Carbon::parse($data->transaction_at, 'Asia/Jakarta')->format('Y-m-d\TH:i');
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
        "remark" => 'required',
        "category_id" => 'required',
        "transaction_type" => 'required',
        "transaction_at" => 'required',
      ], []);
      if ($validate->fails()) {
          return response()->json(['errors'=>$validate->errors()->all()]);
      }

      $data = Transaction::find($id);
      $data->remark = $request->input('remark');
      $data->category_id = $request->input('category_id');
      $data->transaction_at = $request->input('transaction_at');
      if ($request->input('transaction_type') == 'income') {
        $data->debit = $request->input('amount');
        $data->credit = 0;
      } else {
        $data->credit = $request->input('amount');
        $data->debit = 0;
      }
      $data->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
      Transaction::destroy($id);
    }
}
