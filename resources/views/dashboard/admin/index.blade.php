@extends('layouts.main')
@section('content')
<div class="row">
  <h1>Welcome, admin {{ auth()->user()->name ?? 'User' }}</h1>
@endsection
@push('custom-js')
@endpush