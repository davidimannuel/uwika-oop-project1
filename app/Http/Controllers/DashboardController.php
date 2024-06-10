<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
      if (auth()->user()->is_admin) {
        return view('dashboard.admin.index');
      }
      return view('dashboard.index');
    }

    public function incomes_this_year_group_by_month()
    {
      $income_data = [
        [
          "month" => 1,
          "month_name" => "January",
          "total" => 0
        ],
        [
          "month" => 2,
          "month_name" => "February",
          "total" => 0
        ],
        [
          "month" => 3,
          "month_name" => "March",
          "total" => 0
        ],
        [
          "month" => 4,
          "month_name" => "April",
          "total" => 0
        ],
        [
          "month" => 5,
          "month_name" => "May",
          "total" => 0
        ],
        [
          "month" => 6,
          "month_name" => "June",
          "total" => 0
        ],
        [
          "month" => 7,
          "month_name" => "July",
          "total" => 0
        ],
        [
          "month" => 8,
          "month_name" => "August",
          "total" => 0
        ],
        [
          "month" => 9,
          "month_name" => "September",
          "total" => 0
        ],
        [
          "month" => 10,
          "month_name" => "October",
          "total" => 0
        ],
        [
          "month" => 11,
          "month_name" => "November",
          "total" => 0
        ],
        [
          "month" => 12,
          "month_name" => "December",
          "total" => 0
        ]
      ];

      $income = Transaction::selectRaw("sum(debit) as total, date_part('month',transaction_at) as month")
          ->where('debit', '>'  , 0)
          ->whereYear('transaction_at', date('Y'))
          ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
          ->where('accounts.user_id', auth()->user()->id)
          ->groupBy('month')
          ->orderBy('month', 'asc')
          ->get();
      
      // fill value from income to income_data, from index 0
      foreach ($income as $item) {
        $income_data[$item->month - 1]['total'] = $item->total;
      }
   

      return response()->json(array(
          'year' => date('Y'),
          'incomes' => $income_data
      ));
    }

    public function expenses_this_year_group_by_month()
    {
      $expense_data = [
        [
          "month" => 1,
          "month_name" => "January",
          "total" => 0
        ],
        [
          "month" => 2,
          "month_name" => "February",
          "total" => 0
        ],
        [
          "month" => 3,
          "month_name" => "March",
          "total" => 0
        ],
        [
          "month" => 4,
          "month_name" => "April",
          "total" => 0
        ],
        [
          "month" => 5,
          "month_name" => "May",
          "total" => 0
        ],
        [
          "month" => 6,
          "month_name" => "June",
          "total" => 0
        ],
        [
          "month" => 7,
          "month_name" => "July",
          "total" => 0
        ],
        [
          "month" => 8,
          "month_name" => "August",
          "total" => 0
        ],
        [
          "month" => 9,
          "month_name" => "September",
          "total" => 0
        ],
        [
          "month" => 10,
          "month_name" => "October",
          "total" => 0
        ],
        [
          "month" => 11,
          "month_name" => "November",
          "total" => 0
        ],
        [
          "month" => 12,
          "month_name" => "December",
          "total" => 0
        ]
      ];

      $expense = Transaction::selectRaw("sum(credit) as total, date_part('month',transaction_at) as month")
          ->where('credit', '>'  , 0)
          ->whereYear('transaction_at', date('Y'))
          ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
          ->where('accounts.user_id', auth()->user()->id)
          ->groupBy('month')
          ->orderBy('month', 'asc')
          ->get();
      
      // fill value from income to income_data
      foreach ($expense as $item) {
        $income_data[$item->month - 1]['total'] = $item->total;
      }

      return response()->json(array(
          'year' => date('Y'),
          'expenses' => $expense_data
      ));
    }

    public function total_incomes_and_expenses_all_time()
    {
      $total_income = Transaction::where('debit', '>'  , 0)
          ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
          ->where('accounts.user_id', auth()->user()->id)
          ->sum('debit');
          
      $total_expense = Transaction::where('credit', '>'  , 0)
          ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
          ->where('accounts.user_id', auth()->user()->id)
          ->sum('credit');

      return response()->json([
        'data' => [
          [
            'name' => 'Total Income',
            'total' => $total_income
          ],
          [
            'name' => 'Total Expense',
            'total' => $total_expense
          ]
        ]
      ]);
    }
}
