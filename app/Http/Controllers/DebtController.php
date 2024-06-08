<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDebtRelation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DebtController extends Controller
{
    public function index()
    {
      $debt_id = request("debt_id");
      if (request()->ajax()) {      
      // if (true) {      
        // get transaction debt relation with transaction and category by transaction id
        $model = Transaction::with('category')
        ->leftJoin('transaction_debt_relations', 'transaction_debt_relations.relation_id', '=', 'transactions.id')
        ->where('transaction_debt_relations.transaction_id', $debt_id);
        // ddd($model);
        return DataTables::of($model)
        ->order(function ($query) {
          $query->orderBy('transactions.transaction_at', 'asc');
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
          $html = '<button type="button" '.$disable.' class="btn btn-primary table-edit-button" data-id="'.$data->relation_id.'"><i class="bi bi-pencil-square"></i></i></button>';
          
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
      $transaction = Transaction::find($debt_id);
      return view('debt.index', compact('transaction'));
    }

    function total() {
      $debt_id = request("debt_id");
      $transaction = Transaction::find($debt_id);
      $total_debt = 0;
      $sum_col = '';
      if ($transaction->debit > 0) {
        $sum_col = 'transactions.credit';
        $total_debt = $transaction->debit;
      } else {
        $sum_col = 'transactions.debit';
        $total_debt = $transaction->credit;
      }
      
      $paid_debt = Transaction::leftJoin('transaction_debt_relations', 'transaction_debt_relations.relation_id', '=', 'transactions.id')
      ->where('transaction_debt_relations.transaction_id', $debt_id)->sum($sum_col);
      return response()->json([
        "data" => [
          "total_debt_formated" => number_format($total_debt, 0, ',', '.'),
          "paid_debt_formated" => number_format($paid_debt, 0, ',', '.'),
        ]
      ]);
    }
}
