@extends('layouts.main')
@section('content')
<div class="row">
  <h1>Welcome, {{ auth()->user()->name ?? 'User' }}</h1>
</div>
@endsection