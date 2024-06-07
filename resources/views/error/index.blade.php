@extends('layouts.main')
@section('content')
<div class="row">
    <div class="col-md-12">
      <div class="alert alert-danger" role="alert">
        {{ $message  }}
      </div>
    </div>
</div>
@endsection