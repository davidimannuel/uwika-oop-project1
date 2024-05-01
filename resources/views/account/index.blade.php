@extends('layouts.main')
@push('custom-css')
<link href="{{ asset('DataTables/datatables.min.css') }}"  rel="stylesheet">
@endpush

@section('content')
<div class="row">
  <h1>accounts page</h1>
</div>
@endsection

@push('custom-js')
<script src="{{ asset('DataTables/datatables.min.js') }}"></script>
@endpush
