<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
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
          $start_date = $this->convert_timezone($start_date, 'Asia/Jakarta');
          $end_date = $this->convert_timezone($end_date, 'Asia/Jakarta');
          //set end date to end of the day
          $end_date->setTime(23, 59, 59);
          $model->where('transaction_at', '>=', $start_date)->where('transaction_at', '<=', $end_date);
        }
        return DataTables::of($model)
        ->order(function ($query) {
          if (!request()->has('updated_at')) {
              $query->orderBy('updated_at', 'desc');
          }
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
        ->addColumn('category_style',function($data){
          $html = "";
          // Use a ternary operation to determine the status based on the conditions
          $bgColor = ($data->id % 4 == 0) ? 'success' : (($data->id % 3 == 0) ? 'info' : (($data->id % 2 == 0) ? 'dark' : 'primary'));
          $html = $html . '<span class="badge bg-'.$bgColor.'">'.$data->category->name.'</span>';
          return $html;
        })
        ->setRowClass('text-center')
        ->rawColumns(['poster','action','transaction_type','category_style']) // render as raw html instead of string
        ->toJson();
      }
      return view('transaction.index');
    }

    
    public function convert_timezone($date,$tz) {
      $new_date = new DateTime($date, new DateTimeZone($tz));
      return $new_date;
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
        $start_date = $this->convert_timezone($start_date, 'Asia/Jakarta');
        $end_date = $this->convert_timezone($end_date, 'Asia/Jakarta');
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
      fputcsv($handle, array('ID', 'Remark', 'Category',  'Debit', 'Credit', 'Transaction At'));
      foreach($data as $row) {
        fputcsv($handle, array($row['id'], $row['remark'], $row['category']['name'], $row['debit'], $row['credit'] , $row['transaction_at']));
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
      Transaction::destroy($id);
    }
}
