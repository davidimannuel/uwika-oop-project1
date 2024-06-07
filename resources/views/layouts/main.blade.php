<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @stack('custom-meta')
    <title>My App</title>
    <link href="{{ asset('bootstrap-5.3.3-dist/css/bootstrap.min.css') }}"  rel="stylesheet">
    <link href="{{ asset('bootstrap-icons-1.11.3/font/bootstrap-icons.min.css') }}"  rel="stylesheet">
    @stack('custom-css')
  </head>
  <body>
    @auth
      @if (auth()->user()->status != \App\Models\User::STATUS_ACTIVE) 
      <div class="alert alert-warning" role="alert">
        your account is not active, please wait the administrator to activate your account for access full features
      </div>
      @endif
    @endauth
    <nav class="navbar navbar-expand-lg bg-info-subtle">
      <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('home') }}">My App</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav">
            @auth
              @if (auth()->user()->is_admin) 
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('user*') ? 'active' : '' }}" href="{{ route('user.index') }}">User</a>
                </li> 
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('user*') ? 'active' : '' }}" href="{{ route('transaction.admin.index') }}">User Transaction</a>
                </li> 
              @else
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('account*') ? 'active' : '' }}" href="{{ route('account.index') }}">Account</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('category*') ? 'active' : '' }}" href="{{ route('category.index') }}">Category</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('transaction*') ? 'active' : '' }}" href="{{ route('transaction.index') }}">Transaction</a>
                </li>
              @endif
            @endauth
          </ul>
          <ul class="navbar-nav ms-auto">
            @auth
            <li class="nav-item">
              <li class="nav-item">
                <form action="{{ route('logout') }}" method="post">
                  @csrf
                  <button class="nav-link" type="submit"><i class="bi bi-box-arrow-in-right"></i>logout</button>
                </form>
              </li>
            </li>
            @else   
            <li class="nav-item">
              <a class="nav-link" href="{{ route('register') }}"><i class="bi bi-person-plus-fill"></i></i>Register</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i>Login</a>
            </li>
            @endauth
          </ul>
        </div>
      </div>
    </nav>

    <div class="container mt-4">
      @yield('content')
      @stack('after-content')
    </div>

    <script src="{{ asset('bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('jquery/jquery-3.7.1.min.js') }}"></script>
    @stack('custom-js')
  </body>
</html>