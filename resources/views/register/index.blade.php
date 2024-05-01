@extends('layouts.main')

@push('custom-css')
<link href="{{ asset('css/register.css') }}"  rel="stylesheet">
@endpush

@section('content')
<main class="form-signin w-100 m-auto">
  <form method="POST" action="{{ route("register.store") }}">
    @csrf
    <h1 class="h3 mb-3 fw-normal">Please register</h1>
    <div class="form-floating">
      <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}">
      <label for="name">Name</label>
      @error('name')
        <div class="invalid-feedback">
          {{ $message }}
        </div>
      @enderror
    </div>
    <div class="form-floating">
      <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
      <label for="email">Email address</label>
      @error('email')
        <div class="invalid-feedback">
          {{ $message }}
        </div>
      @enderror
    </div>
    <div class="form-floating">
      <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
      <label for="password">Password</label>
      @error('password')
        <div class="invalid-feedback">
          {{ $message }}
        </div>
      @enderror
    </div>
    <div class="form-floating">
      <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation">
      <label for="password_confirmation">Password Confirmation</label>
      @error('password_confirmation')
        <div class="invalid-feedback">
          {{ $message }}
        </div>
      @enderror
    </div>

    <button class="btn btn-primary w-100 py-2" type="submit">Register</button>
    <!-- <p class="mt-5 mb-3 text-body-secondary">&copy; 2017–2024</p> -->
  </form>
</main>
@endsection
