@extends('layouts.main')
@push('custom-meta')
  <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@push('custom-css')
<link href="{{ asset('DataTables/datatables.min.css') }}"  rel="stylesheet">
<link href="{{ asset('sweetalert2-11.10.8/sweetalert2.min.css') }}"  rel="stylesheet">
@endpush

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">user list</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
              <div class="col-md-12 table-responsive">
                <table id="table-list" class="table">
                  <thead>
                    <tr>
                      <th class="text-center">No</th>
                      <th class="text-center">Name</th>
                      <th class="text-center">Created At</th>
                      <th class="text-center">Updated At</th>
                      <th class="text-center">Status</th>
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
<script>
  $(document).ready(function() {
    // datatable
    var datatableList = $('#table-list').DataTable({
      processing: true,
      serverSide: true,
      ajax: '{{ route('user.index') }}',
      columns: [
        { data: 'DT_RowIndex',searchable: false, orderable: false},
        { data: 'name', name: 'name' },
        { data: 'created_at', name: 'created_at'},
        { data: 'updated_at', name: 'updated_at'},
        { data: 'status_action', searchable: false, orderable: false},
      ],
      drawCallback: function (settings) { // add event listener after render datatable "https://datatables.net/reference/option/drawCallback"
        // using class property instead id, the button is more than one
        $(".table-status-button").click(function(){
          Swal.fire({
            title: "Are you sure want to patch status?",
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
              var ajaxUrl = '{{ route("user.patch_status", ":id") }}';
              ajaxUrl = ajaxUrl.replace(':id', id);
              $.ajax({
                  url: ajaxUrl,
                  type: 'PATCH',
                  data: {
                    _method: 'PATCH' // used for laravel accpet PATCH method
                  },
                  processData: true, // required
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
      }
    });
  })
</script>
@endpush
