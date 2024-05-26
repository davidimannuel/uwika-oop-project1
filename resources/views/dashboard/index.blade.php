@extends('layouts.main')
@section('content')
<div class="row">
  <h1>Welcome, {{ auth()->user()->name ?? 'User' }}</h1>
  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Total income and expense all time</h5>
        <canvas id="total-incomes-and-expenses-all-time"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="row">
      <div class="card col-md-12">
        <div class="card-body">
          <h5 class="card-title">Total incomes this Year</h5>
          {{-- <h6 class="card-subtitle mb-2 text-body-secondary">Card subtitle</h6> --}}
          <canvas id="total-incomes-this-year"></canvas>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="card col-md-12">
        <div class="card-body">
          <h5 class="card-title">Total expenses this Year</h5>
          {{-- <h6 class="card-subtitle mb-2 text-body-secondary">Card subtitle</h6> --}}
          <canvas id="total-expenses-this-year"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@push('custom-js')
<script src="{{ asset('chartjs-4.4.1/chart.umd.js') }}"></script>
<script>
$(document).ready(function() {
  $.ajax({
    url: "{{ route('dashboard.incomes_this_year_group_by_month') }}",
    type: 'GET',
    success: function(response) {
      var ctx = document.getElementById('total-incomes-this-year').getContext('2d');
      var total_incomes_this_year_labels = [];
      var total_incomes_this_year_data = [];
      var chart_label = response.year;
      response.incomes.forEach(element => {
        total_incomes_this_year_labels.push(element.month_name);
        total_incomes_this_year_data.push(element.total);
      });

      var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: total_incomes_this_year_labels,
          datasets: [{
            label: chart_label,
            data: total_incomes_this_year_data,
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    }
  });
  
  $.ajax({
    url: "{{ route('dashboard.expenses_this_year_group_by_month') }}",
    type: 'GET',
    success: function(response) {
      var ctx = document.getElementById('total-expenses-this-year').getContext('2d');
      var total_expenses_this_year_labels = [];
      var total_expenses_this_year_data = [];
      var chart_label = response.year;
      response.expenses.forEach(element => {
        total_expenses_this_year_labels.push(element.month_name);
        total_expenses_this_year_data.push(element.total);
      });

      var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: total_expenses_this_year_labels,
          datasets: [{
            label: chart_label,
            data: total_expenses_this_year_data,
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    }
  });
  
  $.ajax({
    url: "{{ route('dashboard.total_incomes_and_expenses_all_time') }}",
    type: 'GET',
    success: function(response) {
      var ctx = document.getElementById('total-incomes-and-expenses-all-time').getContext('2d');
      var chart_labels = [];
      var chart_data = [];
      response.data.forEach(element => {
        chart_labels.push(element.name);
        chart_data.push(element.total);
      });

      var myChart = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: chart_labels,
          datasets: [{
            label: 'Total Incomes and Expenses All Time',
            data: chart_data,
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    }
  });
});
</script>
@endpush