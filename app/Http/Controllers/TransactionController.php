<?php

namespace App\Http\Controllers;

use App\Helpers\TimeHelper;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionDebtRelation;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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
      $start_date = request("start_date");
      $end_date = request("end_date");

      if (request()->ajax())
      {
        $model = Transaction::with('category')->where('account_id', $account_id);
        if ($start_date && $end_date) {
          // parse start_date and end_date to Asia/Jakarta timezone
          $start_date = TimeHelper::convertTimezone($start_date, 'Asia/Jakarta');
          $end_date = TimeHelper::convertTimezone($end_date, 'Asia/Jakarta');
          //set end date to end of the day
          $end_date->setTime(23, 59, 59);
          $model->where('transaction_at', '>=', $start_date)->where('transaction_at', '<=', $end_date);
        }
        return DataTables::of($model)
        ->order(function ($query) {
          $query->orderBy('transaction_at', 'asc');
        })
        ->addIndexColumn()
        ->addColumn('transaction_type',function($data){
          $type = $data->debit > 0 ? 'income' : 'expense';
          $bgColor = $type == 'income' ? 'success' : 'danger';
          return '<span class="badge bg-'.$bgColor.'">'.$type.'</span>';
        })
        ->addColumn('amount',function($data){
          $amount = $data->debit > 0 ? $data->debit : $data->credit;
          // format amount to currency
          return number_format($amount, 0, ',', '.');
        })
        ->addColumn('action',function($data){
          $disable = (auth()->user()->status == User::STATUS_ACTIVE) ? '' : 'disabled';
          $html = '<button type="button" '.$disable.' class="btn btn-primary table-edit-button" data-id="'.$data->id.'"><i class="bi bi-pencil-square"></i></i></button>
          <button type="button" '.$disable.' class="btn btn-danger table-delete-button" data-id="'.$data->id.'"><i class="bi bi-trash-fill"></i></button>';
          if ($data->is_debt) {
            $html = $html . '<a href="'.route("debt.index")."?debt_id=$data->id".'" class="btn btn-warning table-debt-button" ><i class="bi bi-journal-text"></i></a>';
          } 
          return $html;
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
        ->addColumn('category_style',function($data){
          $html = "";
          // Use a ternary operation to determine the status based on the conditions
          $bgColor = ($data->id % 4 == 0) ? 'success' : (($data->id % 3 == 0) ? 'info' : (($data->id % 2 == 0) ? 'dark' : 'primary'));
          $html = $html . '<span class="badge bg-'.$bgColor.'">'.$data->category->name.'</span>';
          return $html;
        })
        ->setRowClass('text-center')
        ->rawColumns(['action','transaction_type','category_style']) // render as raw html instead of string
        ->toJson();
      }
      // get account
      $account = Account::where('user_id', auth()->user()->id)->where('id',request('account_id'))->first();
      // if not found get first account
      if (!$account) {
        $account = Account::where('user_id', auth()->user()->id)->first();
      }
      // if still not found return error view
      if (!$account) {
        return view('error.index', [
          'message' => 'Please create account first',
        ]);
      }
      return view('transaction.index',[
        'account' => $account,
      ]);
    }



    public function total()
    {
      $account_id = request("account_id");
      $total_income = Transaction::where('account_id', $account_id)->where('debit', '>', 0);
      $total_expense = Transaction::where('account_id', $account_id)->where('credit', '>', 0);
      $start_date = request("start_date");
      $end_date = request("end_date");
      if ($start_date && $end_date) {
        // parse start_date and end_date to Asia/Jakarta timezone
        // parse start_date and end_date to Asia/Jakarta timezone
        $start_date = TimeHelper::convertTimezone($start_date, 'Asia/Jakarta');
        $end_date = TimeHelper::convertTimezone($end_date, 'Asia/Jakarta');
        //set end date to end of the day
        $end_date->setTime(23, 59, 59);
        $total_income->where('transaction_at', '>=', $start_date)->where('transaction_at', '<=', $end_date);
        $total_expense->where('transaction_at', '>=', $start_date)->where('transaction_at', '<=', $end_date);
      }
      $total_income_data = $total_income->sum('debit');
      $total_expense_data = $total_expense->sum('credit');
      $balance = $total_income_data - $total_expense_data;
      return response()->json([
        "data" => [
          "total_income" => $total_income_data,
          "total_expense" => $total_expense_data,
          "balance" => $balance,
          // format total expense and income to currency
          "total_income_formated" => number_format($total_income_data, 0, ',', '.'),
          "total_expense_formated" => number_format($total_expense_data, 0, ',', '.'),
          "balance_formated" => number_format($balance, 0, ',', '.'),
        ]
      ]);
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
        "amount" => 'required|gt:0',
      ], []);
      if ($validate->fails()) {
          return response()->json(['errors'=>$validate->errors()->all()]);
      }

      $data = [
        "remark" => $request->input('remark'),
        "account_id" => $request->input('account_id'),
        "category_id" => $request->input('category_id'),
        "transaction_at" => $request->input('transaction_at'),
        "is_debt" => $request->has('is_debt'),
      ];

      if ($request->input('transaction_type') == 'income') {
        $data['debit'] = $request->input('amount');
      } else {
        $data['credit'] = $request->input('amount');
      }

      DB::transaction(function () use ($request,$data) {
        $transaction = Transaction::create($data);
        if ($request->has('debt_id')) {
          $debt_data = [
            'transaction_id' => $request->input('debt_id'),
            'relation_id' => $transaction->id,
          ];
          TransactionDebtRelation::create($debt_data);
        }
      });

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
        "amount" => 'required|gt:0',
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

    public function export_csv(Request $request) {
      $account_id = $request->input('account_id');
      $start_date = $request->input('start_date');
      $end_date = $request->input('end_date');
      // parse start_date and end_date to Asia/Jakarta timezone
      $start_date = Carbon::parse($start_date, 'Asia/Jakarta');
      $end_date = Carbon::parse($end_date, 'Asia/Jakarta');
      $end_date->setTime(23, 59, 59);

      $model = Transaction::with('category')->where('account_id', $account_id);
      if ($start_date && $end_date) {
        $model->where('transaction_at', '>=', $start_date)->where('transaction_at', '<=', $end_date);
      }
      $data = $model->orderBy('transaction_at')->get();
      $filename = 'transaction_'.time().'.csv';
      $handle = fopen($filename, 'w+');
      fputcsv($handle, array('Transaction At', 'Remark', 'Category',  'Debit', 'Credit'));
      foreach($data as $row) {
        fputcsv($handle, array($row['transaction_at'], $row['remark'], $row['category']['name'], $row['debit'], $row['credit']));
      }
      fclose($handle);
      $headers = array(
        'Content-Type' => 'text/csv',
      );
      return response()->download($filename, 'transaction.csv', $headers)->deleteFileAfterSend(true);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
      $transaction = Transaction::find($id);
      // if not found return
      if (!$transaction) {
        return response()->json(['errors'=>['Transaction not found']]);
      }
      // if transaction is debt, delete the relation
      if ($transaction->is_debt) {
        TransactionDebtRelation::where('transaction_id', $transaction->id)->delete();
        Transaction::destroy($transaction->id);
      } else {
        // if transaction is in relation, return error
        $relation = TransactionDebtRelation::where('relation_id', $transaction->id)->first();
        if ($relation) {
          return response()->json(['errors'=>['Transaction is in debt relation, delete parent debt transaction first']]);
        }
        Transaction::destroy($transaction->id);
      }
      // db transaction 
      // DB::transaction(function () use ($transaction) {
      //   return response()->json(['errors'=>['s is in debt']]);
      //   // if transaction is debt, delete the relation
      //   if ($transaction->is_debt) {
      //     TransactionDebtRelation::where('transaction_id', $transaction->id)->delete();
      //     Transaction::destroy($transaction->id);
      //   } else {
      //     return response()->json(['errors'=>['s is in debt']]);
      //     // if transaction is in relation, return error
      //     $relation = TransactionDebtRelation::where('relation_id', $transaction->id)->first();
      //     if ($relation) {
      //       return response()->json(['errors'=>['Transaction is in debt relation']]);
      //     } else {
      //       return response()->json(['errors'=>['Transaction is in debt']]);
      //     }
      //     return response()->json(['errors'=>['s is in debt']]);
      //   }
      // });
    }
}
