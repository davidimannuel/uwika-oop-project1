@extends('layouts.main')
@push('custom-meta')
  <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@push('custom-css')
<link href="{{ asset('DataTables/datatables.min.css') }}"  rel="stylesheet">
<link href="{{ asset('sweetalert2-11.10.8/sweetalert2.min.css') }}"  rel="stylesheet">
<link href="{{ asset('select2-4.0.13/dist/css/select2.min.css') }}"  rel="stylesheet">
<link href="{{ asset('select2-bootstrap-5-theme-1.3.0/select2-bootstrap-5-theme.min.css') }}"  rel="stylesheet">
@endpush

@section('content')
<div class="row mb-2">
  <div class="col-md-2">
    <select class="form-control" name="user" id="user">
    </select>
  </div>
  <div class="col-md-1">
    <p class="text-center">date : </p>
  </div>
  <div class="col-md-2">
    <input type="date" class="form-control" name="start-date" id="start-date">
  </div>
  <div class="col-md-1">
    <p class="text-center">to</p>
  </div>
  <div class="col-md-2">
    <input type="date" class="form-control" name="end-date" id="end-date">
  </div>
  <div class="col-md-2">
    <button type="button" class="btn btn-primary" id="filter-button">filter</button>
  </div>
  <div class="col-md-2">
    <button type="button" class="btn btn-success" id="export-csv-button">Export CSV</button>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">transaction list</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <p class="text-end">Income : </p>
                <p id="total_income" class="text-end fs-5">0</p>
              </div>
              <div class="col-md-3">
                <p class="text-end">Expense : </p>
                <p id="total_expense" class="text-end fs-5">0</p>
              </div>
              <div class="col-md-3">
                <p class="text-end">Balance : </p>
                <p id="balance" class="text-end fs-5">0</p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 table-responsive">
                <table id="table-list" class="table">
                  <thead>
                    <tr>
                      <th class="text-center">No</th>
                      <th class="text-center">Account</th>
                      <th class="text-center">Transaction At</th>
                      <th class="text-center">Remark</th>
                      <th class="text-center">Category</th>
                      <th class="text-center">Type</th>
                      <th class="text-center">Amount</th>
                      <th class="text-center">Created At</th>
                      <th class="text-center">Updated At</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
        </div>
        <!-- /.card-body -->
        <div class="card-footer clearfix">
          <div class="float-right">
          </div>
        </div>
    </div>
  </div>
</div>
@endsection

@push('custom-js')
<script src="{{ asset('DataTables/datatables.min.js') }}"></script>
<script src="{{ asset('sweetalert2-11.10.8/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('select2-4.0.13/dist/js/select2.full.min.js') }}"></script>
<script>
  $(document).ready(function() {
    $('#user').select2({
      placeholder: "Select user",
      theme: 'bootstrap-5',
      minimumInputLength: 0,
      ajax: {
        delay: 250,
        url: '{{ route("user.admin.list") }}',
        dataType: 'json',
        data : function(params){
          var query = {
            search: params.term,
          }

          return query
        },
        processResults: function (response) {
          return {
            results: $.map(response.data,function(data){
              return {
                id: data.id,
                text: data.name,
                selected: false
              }
            })
          };
        }
      }
    });
    
    $('#category').select2({
      placeholder: "Select category",
      theme: 'bootstrap-5',
      dropdownParent: $('#modal-form'), // https://select2.org/troubleshooting/common-problems
      minimumInputLength: 0,
      ajax: {
        delay: 250,
        url: '{{ route("category.list") }}',
        dataType: 'json',
        data : function(params){
          var query = {
            search: params.term,
          }

          return query
        },
        processResults: function (response) {
          return {
            results: $.map(response.data,function(data){
              return {
                id: data.id,
                text: data.name,
                selected: false
              }
            })
          };
        }
      }
    });

    // datatable
    var datatableList = $('#table-list').DataTable({
      processing: true,
      serverSide: true,
      ajax: '{{ route('transaction.admin.index') }}'+'?user='+$('#user').val(),
      columns: [
        { data: 'DT_RowIndex',searchable: false, orderable: false},
        { data: 'account.name', name: 'account.name', orderable: false},
        { data: 'transaction_at', name: 'transaction_at', orderable: false},
        { data: 'remark', name: 'remark', orderable: false},
        { data: 'category_style', searchable: false, orderable: false},
        { data: 'transaction_type', searchable: false, orderable: false},
        { data: 'amount', searchable: false, orderable: false},
        { data: 'created_at', name: 'created_at', orderable: false},
        { data: 'updated_at', name: 'updated_at', orderable: false},
      ]
    });
  
    // function for load total income, expense, and balance
    function loadTotal() {
      $.ajax({
        url: '{{ route('transaction.admin.total') }}'+'?user_id='+$('#user').val()+ '&start_date='+$('#start-date').val()+'&end_date='+$('#end-date').val(),
        type: 'GET',
        success: function(response) {
            // Handle success response
            if (response.errors) {
              Swal.fire({
                  title: "Oops...",
                  text: "error",
                  html: response.errors,
                  icon: "error",
              });
            } else {
              $('#total_income').html(response.data.total_income_formated);
              $('#total_expense').html(response.data.total_expense_formated);
              $('#balance').html(response.data.balance_formated);
            }
        },
        error: function(xhr, status, error) {
            // Handle error response
            console.error(xhr.responseText);
            Swal.fire({
                title: "Oops...",
                text: "Something went wrong!",
                icon: "error",
            });
        }
      });
    }

    //add event listener for elemen <select>
    $('#user').on('change', function() {
        // get value
        var selectedType = $(this).val();
        // configure URL Ajax and adding query parameter "type"
        var ajaxUrl = '{{ route('transaction.admin.index') }}' +'?user_id='+$('#user').val();

        datatableList.ajax.url(ajaxUrl).load();
        loadTotal();
    });

    // add event listener for filter button
    $('#filter-button').click(function(event){
      // prevent default form submit
      event.preventDefault();
      // if not select user yet give warning
      if ($('#user').val() == null) {
        Swal.fire({
          title: "Oops...",
          text: "Please select user first!",
          icon: "warning",
        });
        return;
      }
      // configure URL Ajax and adding query parameter "type"
      var ajaxUrl = '{{ route('transaction.admin.index') }}' +'?user_id='+$('#user').val()+'&start_date='+$('#start-date').val()+'&end_date='+$('#end-date').val();
      datatableList.ajax.url(ajaxUrl).load();
      loadTotal();
    })

    // add event listener for export csv button
    $('#export-csv-button').click(function(event){
      // prevent default form submit
      event.preventDefault();
      // if not select user yet give warning
      if ($('#user').val() == null) {
        Swal.fire({
          title: "Oops...",
          text: "Please select user first!",
          icon: "warning",
        });
        return;
      }
      // validate start date and end date
      if ($('#start-date').val() == '' || $('#end-date').val() == '') {
        Swal.fire({
          title: "Oops...",
          text: "Please select start date and end date!",
          icon: "warning",
        });
        return;
      }
      // configure URL Ajax and adding query parameter "type"
      var ajaxUrl = '{{ route('transaction.admin.exportCsv') }}' +'?user_id='+$('#user').val()+'&start_date='+$('#start-date').val()+'&end_date='+$('#end-date').val();
      window.location.href = ajaxUrl;
    })
  })
</script>
@endpush
