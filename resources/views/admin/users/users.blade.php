<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
        <meta
          content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
          name="viewport"
        />
        <link
          rel="icon"
          href="{{asset('build/dashassets/img/kaiadmin/favicon.ico')}}"
          type="image/x-icon"
        />

        <!-- Fonts and icons -->
        <script src="{{asset('build/dashassets/js/plugin/webfont/webfont.min.js')}}"></script>
        <script>
          WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
              families: [
                "Font Awesome 5 Solid",
                "Font Awesome 5 Regular",
                "Font Awesome 5 Brands",
                "simple-line-icons",
              ],
              urls: ["{{asset('build/dashassets/css/fonts.min.css')}}"],
            },
            active: function () {
              sessionStorage.fonts = true;
            },
          });
        </script>

        <!-- CSS Files -->
        <link rel="stylesheet" href="{{asset('build/dashassets/css/bootstrap.min.css')}}" />
        <link rel="stylesheet" href="{{asset('build/dashassets/css/plugins.min.css')}}" />
        <link rel="stylesheet" href="{{asset('build/dashassets/css/kaiadmin.min.css')}}" />

        <!-- CSS Just for demo purpose, don't include it in your project -->
        <link rel="stylesheet" href="{{asset('build/dashassets/css/demo.css')}}" />
      </head>
      <body>
        <div class="wrapper">
          <!-- Sidebar -->
            @include('inc.admin.sidebar')
          <!-- End Sidebar -->

          <div class="main-panel">
            <div class="main-header">
              <div class="main-header-logo">
                <!-- Logo Header -->
                <div class="logo-header" data-background-color="dark">
                  <a href="index.html" class="logo">
                    <img
                      src="{{asset('build/dashassets/img/kaiadmin/logo_light.svg')}}"
                      alt="navbar brand"
                      class="navbar-brand"
                      height="20"
                    />
                  </a>
                  <div class="nav-toggle">
                    <button class="btn btn-toggle toggle-sidebar">
                      <i class="gg-menu-right"></i>
                    </button>
                    <button class="btn btn-toggle sidenav-toggler">
                      <i class="gg-menu-left"></i>
                    </button>
                  </div>
                  <button class="topbar-toggler more">
                    <i class="gg-more-vertical-alt"></i>
                  </button>
                </div>
                <!-- End Logo Header -->
              </div>
              @include('inc.admin.navbar')
              <!-- Navbar Header -->

              <!-- End Navbar -->
            </div>

            <div class="container">
              <div class="page-inner">
                <h2>Liste Utilisateurs</h2>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-head-bg-warning mt-4">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nom</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Etat</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($users as $index => $u)

                        <tr>
                            <td>{{ $index+1 }}</td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->role }}</td>
                            <td>
                                <span class="badge {{ $u->state == 'NonActif' ? 'badge-danger' : ($u->state == 'Actif' ? 'badge-success' : '') }} ">{{ $u->state }}</span>
                            </td>

                            <td>
                                <div class="d-flex">
                                <a data-bs-toggle="modal" data-bs-target="#editUser{{ $u->id }}" class="btn btn-link btn-primary btn-lg" data-original-title="Edit Task">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a onclick="return confirm('Voulez-vous vraiment supprimer le compte de cet utilisateur?')" href="{{ route('users.delete', ['id' => $u->id]) }}" class="btn btn-link btn-danger">
                                    <i class="fa fa-times"></i>
                                <a>
                                </div>
                            </td>
                        </tr>

                        @endforeach
                        </tbody>
                  </table>
                </div>
              </div>
            </div>

            <footer class="footer">
              <div class="container-fluid d-flex justify-content-between">
                <nav class="pull-left">
                  <ul class="nav">
                    <li class="nav-item">
                      <a class="nav-link" href="http://www.themekita.com">
                        ThemeKita
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#"> Help </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#"> Licenses </a>
                    </li>
                  </ul>
                </nav>
                <div class="copyright">
                  2024, made with <i class="fa fa-heart heart text-danger"></i> by
                  <a href="http://www.themekita.com">ThemeKita</a>
                </div>
                <div>
                  Distributed by
                  <a target="_blank" href="https://themewagon.com/">ThemeWagon</a>.
                </div>
              </div>
            </footer>
          </div>
        </div>


          <!-- Modal Modifier -->
          @foreach($users as $index=>$u)
          <!-- Modal Modifier -->
        <div class="modal fade" id="editUser{{ $u->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Modifier l'utilisateur <span class="text-primary">{{$u->name}}</span></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form  action="{{ route('users.update') }}" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="modal-body">
                      <div class="row">
                          <div class="col form-group">
                              <label for="name">Nom:</label>
                              <input type="text" class="form-control input-full" id="name" name="name"  value="{{ $u->name }}">
                              @error('name')
                              <p class="text-danger">
                                  {{ $message }}
                              </p>
                              @enderror
                          </div>
                          <div class="col form-group">
                              <label for="email">email</label>
                              <input type="email" class="form-control input-full" id="email" name="email" placeholder="Enter Input" value="{{ $u->email }}">
                              @error('email')
                              <p class="text-danger">
                                  {{ $message }}
                              </p>
                              @enderror
                          </div>
                      </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role" id="role1" {{ $u->role == 'client' ? 'checked' : '' }} value="client">
                                <label class="form-check-label" for="role1">
                                Client
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role" id="role2" {{ $u->role == 'vendeur' ? 'checked' : '' }} value="vendeur">
                                <label class="form-check-label" for="role2">
                                Vendeur
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role" id="role3" {{ $u->role == 'admin' ? 'checked' : '' }} value="admin">
                                <label class="form-check-label" for="role3">
                                Admin
                                </label>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="state" name="state" value="{{$u->state}}" {{ $u->state == 'Actif' ? 'checked' : '' }}>
                                <label class="form-check-label" for="state">{{ $u->state }}</label>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" value="{{ $u->id }}" name="id_user">

                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-danger btn-border" data-bs-dismiss="modal">Annuler</button>
                      <button type="submit" class="btn btn-primary">Modifier</button>
                  </div>
              </form>
            </div>
          </div>
        </div>



        @endforeach
        <!--   Core JS Files   -->
        <script src="{{asset('build/dashassets/js/core/jquery-3.7.1.min.js')}}"></script>
        <script src="{{asset('build/dashassets/js/core/popper.min.js')}}"></script>
        <script src="{{asset('build/dashassets/js/core/bootstrap.min.js')}}"></script>

        <!-- jQuery Scrollbar -->
        <script src="{{asset('build/dashassets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js')}}"></script>

        <!-- Chart JS -->
        <script src="{{asset('build/dashassets/js/plugin/chart.js/chart.min.js')}}"></script>

        <!-- jQuery Sparkline -->
        <script src="{{asset('build/dashassets/js/plugin/jquery.sparkline/jquery.sparkline.min.js')}}"></script>

        <!-- Chart Circle -->
        <script src="{{asset('build/dashassets/js/plugin/chart-circle/circles.min.js')}}"></script>

        <!-- Datatables -->
        <script src="{{asset('build/dashassets/js/plugin/datatables/datatables.min.js')}}"></script>

        <!-- Bootstrap Notify -->
        <script src="{{asset('build/dashassets/js/plugin/bootstrap-notify/bootstrap-notify.min.js')}}"></script>

        <!-- jQuery Vector Maps -->
        <script src="{{asset('build/dashassets/js/plugin/jsvectormap/jsvectormap.min.js')}}"></script>
        <script src="{{asset('build/dashassets/js/plugin/jsvectormap/world.js')}}"></script>

        <!-- Sweet Alert -->
        <script src="{{asset('build/dashassets/js/plugin/sweetalert/sweetalert.min.js')}}"></script>

        <!-- Kaiadmin JS -->
        <script src="{{asset('build/dashassets/js/kaiadmin.min.js')}}"></script>

        <!-- Kaiadmin DEMO methods, don't include it in your project! -->
        <script>
          $("#lineChart").sparkline([102, 109, 120, 99, 110, 105, 115], {
            type: "line",
            height: "70",
            width: "100%",
            lineWidth: "2",
            lineColor: "#177dff",
            fillColor: "rgba(23, 125, 255, 0.14)",
          });

          $("#lineChart2").sparkline([99, 125, 122, 105, 110, 124, 115], {
            type: "line",
            height: "70",
            width: "100%",
            lineWidth: "2",
            lineColor: "#f3545d",
            fillColor: "rgba(243, 84, 93, .14)",
          });

          $("#lineChart3").sparkline([105, 103, 123, 100, 95, 105, 115], {
            type: "line",
            height: "70",
            width: "100%",
            lineWidth: "2",
            lineColor: "#ffa534",
            fillColor: "rgba(255, 165, 52, .14)",
          });
        </script>
      </body>
    </html>

