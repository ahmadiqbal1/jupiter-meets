<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/fa.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/ionicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/responsive.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/toastr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('storage/images/FAVICON.png') }}">
    @yield('style')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                        </li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            @if (Auth::user()->role == 'admin')
                                <li class="nav-item">
                                    <a class="nav-link active" href="{{ route('admin') }}">{{ __('Admin') }}</a>
                                </li>
                            @endif

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>

                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->username }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('changePassword') }}">
                                        Change Password
                                    </a>

                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('admin') }}" class="brand-link">
              <img src="{{ asset('storage/images/SECONDARY_LOGO.png') }}" alt="{{ getSetting('APPLICATION_NAME') }}" class="brand-image img-circle elevation-3" style="opacity: .8">
              <span class="brand-text font-weight-light">{{ getSetting('APPLICATION_NAME') }}</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="{{ route('admin') }}" class="nav-link" data-name="admin">
                              <i class="nav-icon fas fa-tachometer-alt"></i>
                              <p>
                                Dashboard
                              </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('meetings') }}" class="nav-link" data-name="meetings">
                              <i class="nav-icon fa fa-video"></i>
                              <p>
                                Meetings
                              </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('users') }}" class="nav-link" data-name="users">
                              <i class="nav-icon fa fa-users"></i>
                              <p>
                                Users
                              </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('income') }}" class="nav-link" data-name="income">
                              <i class="nav-icon fa fa-dollar-sign"></i>
                              <p>
                                Income
                              </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('global-config') }}" class="nav-link" data-name="global-config">
                              <i class="nav-icon fa fa-cog"></i>
                              <p>
                                Global Configuration
                              </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('signaling') }}" class="nav-link" data-name="signaling">
                              <i class="nav-icon fa fa-signal"></i>
                              <p>
                                Signaling Server
                              </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('content') }}" class="nav-link" data-name="content">
                              <i class="nav-icon fa fa-file-alt"></i>
                              <p>
                                Content
                              </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('update') }}" class="nav-link" data-name="update">
                              <i class="nav-icon fa fa-download"></i>
                              <p>
                                Manage Update
                              </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('license') }}" class="nav-link" data-name="license">
                              <i class="nav-icon fa fa-id-badge"></i>
                              <p>
                                Manage License
                              </p>
                            </a>
                        </li>
                   </ul>
               </nav>
            </div>
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
              <div class="container-fluid">
                <div class="row">
                  <div class="col-sm-6">
                    <h1 class="m-0">@yield('page')</h1>
                  </div><!-- /.col -->
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
                      <li class="breadcrumb-item active">@yield('page')</li>
                    </ol>
                  </div><!-- /.col -->
                </div><!-- /.row -->
              </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
              <div class="container-fluid">
                    @yield('content')
              </div>
            </section>
        </div>
        <!-- /.content-wrapper -->

      <footer class="main-footer">
        <strong>Copyright &copy; {{ date('Y') }} {{ getSetting('APPLICATION_NAME') }}.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
          <b>Version</b> {{ getSetting('VERSION') }}
        </div>
      </footer>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/app.min.js') }}"></script>
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/toastr.min.js') }}"></script>
    <script src="{{ asset('js/common.js') }}"></script>
    @yield('script')
</body>
</html>
