<?php

namespace App\Http\Controllers;

use App\Helpers\TimeHelper;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TransactionAdminController extends Controller
{
  public function index()
  {
    $user_id = request("user_id");
    $start_date = request("start_date");
    $end_date = request("end_date");

    if (request()->ajax())
    {
      // get by user
      $model = Transaction::with('category','account')
        ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
        ->where('accounts.user_id', $user_id);
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
    return view('transaction.admin.index');
  }

  public function total()
    {
      $user_id = request("user_id");
      $total_income = Transaction::with('category')
        ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
        ->where('accounts.user_id', $user_id)
        ->where('user_id', $user_id)->where('debit', '>', 0);
      $total_expense = Transaction::with('category')
        ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
        ->where('accounts.user_id', $user_id)
        ->where('user_id', $user_id)->where('credit', '>', 0);
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

    public function export_csv(Request $request) {
      $user_id = request("user_id");
      $start_date = $request->input('start_date');
      $end_date = $request->input('end_date');
      // parse start_date and end_date to Asia/Jakarta timezone
      $start_date = Carbon::parse($start_date, 'Asia/Jakarta');
      $end_date = Carbon::parse($end_date, 'Asia/Jakarta');
      $end_date->setTime(23, 59, 59);

      $model = Transaction::with('category','account')
        ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
        ->where('accounts.user_id', $user_id);
      if ($start_date && $end_date) {
        $model->where('transaction_at', '>=', $start_date)->where('transaction_at', '<=', $end_date);
      }
      $data = $model->orderBy('transaction_at')->get();
      $filename = 'transaction_'.time().'.csv';
      $handle = fopen($filename, 'w+');
      fputcsv($handle, array('Account','Transaction At', 'Remark', 'Category',  'Debit', 'Credit'));
      foreach($data as $row) {
        fputcsv($handle, array($row['account']['name'],$row['transaction_at'], $row['remark'], $row['category']['name'], $row['debit'], $row['credit']));
      }
      fclose($handle);
      $headers = array(
        'Content-Type' => 'text/csv',
      );
      return response()->download($filename, 'transaction.csv', $headers)->deleteFileAfterSend(true);
    }
}
