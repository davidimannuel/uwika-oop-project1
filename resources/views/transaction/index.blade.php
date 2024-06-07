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
    <select class="form-control" name="account" id="account">
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
              <div class="col-md-2 mb-2">
                <!-- Button trigger modal -->
                <button type="button" class="btn btn-success" id="modal-show-button" 
                  @if (Auth::user()->status !== \App\Models\User::STATUS_ACTIVE) disabled @endif>
                  Create
                </button>
              </div>
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
                      <th class="text-center">Transaction At</th>
                      <th class="text-center">Remark</th>
                      <th class="text-center">Category</th>
                      <th class="text-center">Type</th>
                      <th class="text-center">Amount</th>
                      <th class="text-center">Created At</th>
                      <th class="text-center">Updated At</th>
                      <th class="text-center">Action</th>
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

@push('after-content')
<!-- Modal -->
<div class="modal fade" id="modal-form" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form action="">
        <div class="modal-header">
          <h3 class="modal-title fs-5" id="modal-title"></h3>
        </div>
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-12">
                <label for="name">Remark</label>
                <textarea class="form-control" name="remark" id="remark" cols="30" rows="5"></textarea>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <label for="category">Category</label>
                <select class="form-control" name="category" id="category"></select>
              </div>
              <div class="col-md-6">
                <label for="transaction-at">Transaction at</label>
                <input type="datetime-local" class="form-control" name="transaction-at" id="transaction-at">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <label for="transaction-type">Transaction type</label>
                <select class="form-control" name="transaction-type" id="transaction-type">
                  <option value="income">Income</option>
                  <option value="expense">Expense</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="amount">Amount</label>
                <input type="number" class="form-control" name="amount" id="amount">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="" id="is-debt">
                  <label class="form-check-label" for="is-debt">
                    Is Debt ?
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="modal-close-button">Close</button>
          <button type="button" class="btn btn-success" id="modal-save-button" data-edit-id="">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endpush

@push('custom-js')
<script src="{{ asset('DataTables/datatables.min.js') }}"></script>
<script src="{{ asset('sweetalert2-11.10.8/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('select2-4.0.13/dist/js/select2.full.min.js') }}"></script>
<script>
  $(document).ready(function() {
    $('#account').select2({
      placeholder: "Select account",
      theme: 'bootstrap-5',
      minimumInputLength: 0,
      ajax: {
        delay: 250,
        url: '{{ route("account.list") }}',
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

    var accountOpt = new Option('{{ $account->name }}', {{ $account->id }}, true, true); // create the option and append to Select2
    $('#account').append(accountOpt);// https://select2.org/data-sources/ajax#default-pre-selected-values

    //add event listener for elemen <select>
    $('#account').on('change', function() {
        // get value
        var selectedType = $(this).val();
        // configure URL Ajax and adding query parameter "type"
        var ajaxUrl = '{{ route('transaction.index') }}' +'?account_id='+$('#account').val();

        datatableList.ajax.url(ajaxUrl).load();
        loadTotal();
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
    loadTotal();
    // datatable
    var datatableList = $('#table-list').DataTable({
      processing: true,
      serverSide: true,
      ajax: '{{ route('transaction.index') }}'+'?account_id='+'{{ $account->id }}',
      columns: [
        { data: 'DT_RowIndex',searchable: false, orderable: false},
        { data: 'transaction_at', name: 'transaction_at', orderable: false},
        { data: 'remark', name: 'remark', orderable: false},
        { data: 'category_style', searchable: false, orderable: false},
        { data: 'transaction_type', searchable: false, orderable: false},
        { data: 'amount', searchable: false, orderable: false},
        { data: 'created_at', name: 'created_at', orderable: false},
        { data: 'updated_at', name: 'updated_at', orderable: false},
        { data: 'action', searchable: false, orderable: false},
      ],
      drawCallback: function (settings) { // add event listener after render datatable "https://datatables.net/reference/option/drawCallback"
        // using class property instead id, delete button is more than one
        $(".table-delete-button").click(function(){
          Swal.fire({
            title: "Are you sure want to delete?",
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: "Save",
            denyButtonText: `Don't save`
          }).then((result) => {
            if (result.isConfirmed) {
              Swal.showLoading(); // swal show spinner loading
              // Set up CSRF token for AJAX request
              $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
              });
  
              var id = $(this).data('id');
              var ajaxUrl = '{{ route("transaction.destroy", ":id") }}';
              ajaxUrl = ajaxUrl.replace(':id', id);
              $.ajax({
                  url: ajaxUrl,
                  type: 'DELETE',
                  processData: false, // required
                  contentType: false,
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
                        Swal.fire({
                            title: "Success!",
                            text: "Saved!",
                            icon: "success",
                        });
  
                        datatableList.ajax.reload(); // reload datatable
                        loadTotal();
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
          });
        })
        
        $(".table-edit-button").click(function(){
          $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
          });
  
          var id = $(this).data('id');
          var ajaxUrl = '{{ route("transaction.edit", ":id") }}';
          ajaxUrl = ajaxUrl.replace(':id', id);
          $.ajax({
              url: ajaxUrl,
              type: 'GET',
              processData: false, // required
              contentType: false,
              success: function(response) {
                  // Handle success response
                  $("#modal-title").html('edit');
                  $("#modal-form").modal('show');
                  $('#remark').val(response.data.remark);
                  $('#transaction-at').val(response.data.transaction_at_tz);

                  if (response.data.debit > 0) {
                    $('#transaction-type').val('income');
                  } else {
                    $('#transaction-type').val('expense');
                  }

                  if (response.data.debit > 0) {
                    $('#amount').val(response.data.debit);
                  } else {
                    $('#amount').val(response.data.credit);
                  }
                  // Set the selected option in Select2 https://select2.org/programmatic-control/add-select-clear-items#preselecting-options-in-an-remotely-sourced-ajax-select2
                  var categoryOpt = new Option(response.data.category.name, response.data.category.id, true, true); // create the option and append to Select2
                  $('#category').append(categoryOpt).trigger('change');// https://select2.org/data-sources/ajax#default-pre-selected-values

                  if (response.data.is_debt) {
                    $('#is-debt').prop('checked', true);
                  } else {
                    $('#is-debt').prop('checked', false);
                  }
                  // disable is debt
                  $('#is-debt').prop('disabled', true);
                  $("#modal-save-button").attr('data-edit-id',response.data.id);
              },
              error: function(xhr, status, error) {
                  // Handle error response
                  console.error(xhr.responseText);
                  Swal.fire({
                      title: "Oops...",
                      text: "Get data error!",
                      icon: "error",
                  });
              }
          });
        })
      }
    });
  
    // function for load total income, expense, and balance
    function loadTotal() {
      $.ajax({
        url: '{{ route('transaction.total') }}'+'?account_id='+$('#account').val()+ '&start_date='+$('#start-date').val()+'&end_date='+$('#end-date').val(),
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

    // add event listener for filter button
    $('#filter-button').click(function(event){
      // prevent default form submit
      event.preventDefault();
      // if not select account yet give warning
      if ($('#account').val() == null) {
        Swal.fire({
          title: "Oops...",
          text: "Please select account first!",
          icon: "warning",
        });
        return;
      }
      // configure URL Ajax and adding query parameter "type"
      var ajaxUrl = '{{ route('transaction.index') }}' +'?account_id='+$('#account').val()+'&start_date='+$('#start-date').val()+'&end_date='+$('#end-date').val();
      datatableList.ajax.url(ajaxUrl).load();
      loadTotal();
    })

    // add event listener for export csv button
    $('#export-csv-button').click(function(event){
      // prevent default form submit
      event.preventDefault();
      // if not select account yet give warning
      if ($('#account').val() == null) {
        Swal.fire({
          title: "Oops...",
          text: "Please select account first!",
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
      var ajaxUrl = '{{ route('transaction.exportCsv') }}' +'?account_id='+$('#account').val()+'&start_date='+$('#start-date').val()+'&end_date='+$('#end-date').val();
      window.location.href = ajaxUrl;
    })

    // modal
    $("#modal-show-button").click(function(){
      // if not select account yet give warning
      if ($('#account').val() == null) {
        Swal.fire({
          title: "Oops...",
          text: "Please select account first!",
          icon: "warning",
        });
        return;
      }
      $("#modal-title").html('create');
      $("#modal-form").modal('show');
      $('#remark').val('');
      $('#category').val('');
      $('#transaction-at').val('');
      $('#transaction-type').val('');
      $('#amount').val('');
      $("#modal-save-button").attr('data-edit-id','')
      // enable is debt
      $('#is-debt').prop('disabled', false);
    })
    
    $("#modal-close-button").click(function(){
      $("#modal-form").modal('hide');
    })
    
    $("#modal-save-button").click(function(){
      Swal.showLoading(); // swal show spinner loading
      // Set up CSRF token for AJAX request
      $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
  
      var formData = new FormData();
      formData.append('remark', $('#remark').val());
      formData.append('account_id', $('#account').val());
      formData.append('category_id', $('#category').val());
      formData.append('transaction_at', $('#transaction-at').val());
      formData.append('transaction_type', $('#transaction-type').val());
      formData.append('amount', $('#amount').val());
      if ($('#is-debt').is(':checked')) {
        formData.append('is_debt', true);
      }
  
      var editId = parseInt($("#modal-save-button").attr('data-edit-id'));
      var url = '{{ route('transaction.store') }}';
      
      if (editId > 0) {
        formData.append('_method', 'PUT'); // used for method except GET and POST
        url = '{{ route("transaction.update", ":id") }}';
        url = url.replace(':id', editId);
      }
  
      $.ajax({
          url: url,
          type: 'POST',
          enctype: 'multipart/form-data',
          data: formData,
          processData: false, // required
          contentType: false,
          success: function(response) {
              // Handle success response
              if (response.errors) {
                Swal.fire({
                    title: "Oops...",
                    html: response.errors,
                    icon: "error",
                });
              } else {
                Swal.fire({
                    title: "Success!",
                    text: "Saved!",
                    icon: "success",
                });
                datatableList.ajax.reload(); // reload datatable
                loadTotal();
                $("#modal-form").modal('hide'); // close modal
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
    })
  })
</script>
@endpush
