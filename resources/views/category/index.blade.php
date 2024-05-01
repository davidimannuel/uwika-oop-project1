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
            <h3 class="card-title">category list</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
              <div class="col-md-2 mb-2">
                <!-- Button trigger modal -->
                <button type="button" class="btn btn-success" id="modal-show-button">
                  Create
                </button>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 table-responsive">
                <table id="table-list" class="table">
                  <thead>
                    <tr>
                      <th class="text-center">No</th>
                      <th class="text-center">Name</th>
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
                <label for="name">Name</label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Enter name">
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
<script>
  $(document).ready(function() {
    // datatable
    var datatableList = $('#table-list').DataTable({
      processing: true,
      serverSide: true,
      ajax: '{{ route('category.index') }}',
      columns: [
        { data: 'DT_RowIndex',searchable: false, orderable: false},
        { data: 'name', name: 'name' },
        { data: 'created_at', name: 'created_at'},
        { data: 'updated_at', name: 'updated_at'},
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
              var ajaxUrl = '{{ route("category.destroy", ":id") }}';
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
          var ajaxUrl = '{{ route("category.edit", ":id") }}';
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
                  $('#name').val(response.data.name);
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
  
    // modal
    $("#modal-show-button").click(function(){
      $("#modal-title").html('create');
      $("#modal-form").modal('show');
      $('#name').val('');
      $("#modal-save-button").attr('data-edit-id','')
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
      formData.append('name', $('#name').val());
  
      var editId = parseInt($("#modal-save-button").attr('data-edit-id'));
      var url = '{{ route('category.store') }}';
      
      if (editId > 0) {
        formData.append('_method', 'PUT'); // used for method except GET and POST
        url = '{{ route("category.update", ":id") }}';
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
