@extends('layouts.main')

@push('custom-css')
<link href="{{ asset('css/login.css') }}"  rel="stylesheet">
@endpush

@section('content')
<main class="form-signin w-100 m-auto">
  @if (session()->has('register_success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <strong>Register Success</strong> Please login with your email and password
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  
  <form method="POST" action="{{ route('login.authenticate') }}">
    @csrf
    @if (session()->has('loginError'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      invalid <strong>Email / Password</strong> 
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
  
    <h1 class="h3 mb-3 fw-normal">Please sign in</h1>
    <div class="form-floating">
      <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="email">
      <label for="floatingInput">Email address</label>
    </div>
    <div class="form-floating">
      <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password">
      <label for="floatingPassword">Password</label>
    </div>

    <div class="form-check text-start my-3">
      <input class="form-check-input" type="checkbox" id="remember" name="remember">
      <label class="form-check-label" for="remember">
        Remember me
      </label>
    </div>
    <button class="btn btn-primary w-100 py-2" type="submit">Sign in</button>
    <!-- <p class="mt-5 mb-3 text-body-secondary">&copy; 2017–2024</p> -->
  </form>
</main>
@endsection