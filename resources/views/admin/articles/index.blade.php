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
                <div
                  class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4"
                >
               <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Ajouter Article</button>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-head-bg-warning mt-4">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Réference</th>
                            <th scope="col">Désignation</th>
                            <th scope="col">Déscription</th>
                            <th scope="col">Qte_Stock</th>
                            <th scope="col">Qte_Instance</th>
                            <th scope="col">Qte_Reserve</th>
                            <th scope="col">Prix</th>
                            <th scope="col">Photo</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($articles as $index => $a)
                            <tr>
                                <td>{{ $index+1 }}</td>
                                <td>{{ $a->ref }}</td>
                                <td>{{ $a->designation }}</td>
                                <td>{{ $a->description }}</td>
                                <td>{{ $a->qte_stock }}</td>
                                <td>{{ $a->qte_instance }}</td>
                                <td>{{ $a->qte_reserve }}</td>
                                <td>{{ $a->prix }}</td>
                                <td>photo</td>
                                <td>
                                    <div class="d-flex">
                                    <a data-bs-toggle="modal" data-bs-target="#editArticle{{ $a->id }}" class="btn btn-link btn-primary btn-lg" data-original-title="Edit Task">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a onclick="return confirm('Voulez-vous vraiment supprimer cet article?')" href="{{ route('articles.delete', ['id' => $a->id]) }}" class="btn btn-link btn-danger">
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

          <!-- Custom template | don't include it in your project! -->
          <div class="custom-template">
            <div class="title">Settings</div>
            <div class="custom-content">
              <div class="switcher">
                <div class="switch-block">
                  <h4>Logo Header</h4>
                  <div class="btnSwitch">
                    <button
                      type="button"
                      class="selected changeLogoHeaderColor"
                      data-color="dark"
                    ></button>
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="blue"
                    ></button>
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="purple"
                    ></button>
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="light-blue"
                    ></button>
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="green"
                    ></button>
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="orange"
                    ></button>
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="red"
                    ></button>
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="white"
                    ></button>
                    <br />
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="dark2"
                    ></button>
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="blue2"
                    ></button>
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="purple2"
                    ></button>
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="light-blue2"
                    ></button>
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="green2"
                    ></button>
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="orange2"
                    ></button>
                    <button
                      type="button"
                      class="changeLogoHeaderColor"
                      data-color="red2"
                    ></button>
                  </div>
                </div>
                <div class="switch-block">
                  <h4>Navbar Header</h4>
                  <div class="btnSwitch">
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="dark"
                    ></button>
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="blue"
                    ></button>
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="purple"
                    ></button>
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="light-blue"
                    ></button>
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="green"
                    ></button>
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="orange"
                    ></button>
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="red"
                    ></button>
                    <button
                      type="button"
                      class="selected changeTopBarColor"
                      data-color="white"
                    ></button>
                    <br />
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="dark2"
                    ></button>
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="blue2"
                    ></button>
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="purple2"
                    ></button>
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="light-blue2"
                    ></button>
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="green2"
                    ></button>
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="orange2"
                    ></button>
                    <button
                      type="button"
                      class="changeTopBarColor"
                      data-color="red2"
                    ></button>
                  </div>
                </div>
                <div class="switch-block">
                  <h4>Sidebar</h4>
                  <div class="btnSwitch">
                    <button
                      type="button"
                      class="changeSideBarColor"
                      data-color="white"
                    ></button>
                    <button
                      type="button"
                      class="selected changeSideBarColor"
                      data-color="dark"
                    ></button>
                    <button
                      type="button"
                      class="changeSideBarColor"
                      data-color="dark2"
                    ></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- End Custom template -->
        </div>


          <!-- Modal Ajout -->
          <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="exampleModalLabel">Ajouter un article</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form  action="{{ route('articles.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col form-group">
                                <label for="ref">Réf:</label>
                                <input type="text" class="form-control input-full" id="ref" name="ref" placeholder="Enter Input">
                                @error('ref')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div class="col form-group">
                                <label for="designation">Désignation:</label>
                                <input type="text" class="form-control input-full" id="designation" name="designation" placeholder="Enter Input">
                                @error('designation')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col form-group">
                                <label for="stock">Qte_Stock:</label>
                                <input type="number" class="form-control input-full" id="stock" name="stock" placeholder="Enter Input">
                                @error('stock')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div class="col form-group">
                                <label for="instance">Qte_Instance:</label>
                                <input type="number" class="form-control input-full" id="instance" name="instance" placeholder="Enter Input">
                                @error('instance')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col form-group">
                                <label for="reserve">Qte_Reserve:</label>
                                <input type="number" class="form-control input-full" id="reserve" name="reserve" placeholder="Enter Input">
                                @error('reserve')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div class="col form-group">
                                <label for="prix">prix:</label>
                                <input type="text" class="form-control input-full" id="prix" name="prix" placeholder="Enter Input">
                                @error('prix')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                        {{-- <div class="row">
                            <div class="col form-group">
                                <label for="photo">photo:</label>
                                <input type="file" class="form-control-file" id="photo" name="photo">
                                @error('photo')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div> --}}
                        <div class="row">
                            <div class="col form-group">
                                <label for="description">Description:</label>
                                <textarea class="form-control" id="description" name="description" rows="5">                          </textarea>
                                @error('description')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Soumettre</button>
                    </div>
                </form>
              </div>
            </div>
          </div>

          @foreach($articles as $index=>$a)
            <!-- Modal Modifier -->
          <div class="modal fade" id="editArticle{{$a->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="exampleModalLabel">Modifier l'article <span class="text-primary">{{$a->ref}}</span></h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form  action="{{ route('articles.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col form-group">
                                <label for="ref">Réf:</label>
                                <input type="text" class="form-control input-full" id="ref" name="ref"  value="{{ $a->ref }}">
                                @error('ref')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div class="col form-group">
                                <label for="designation">Désignation:</label>
                                <input type="text" class="form-control input-full" id="designation" name="designation" placeholder="Enter Input" value="{{ $a->designation }}">
                                @error('designation')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col form-group">
                                <label for="stock">Qte_Stock:</label>
                                <input type="number" class="form-control input-full" id="stock" name="stock" placeholder="Enter Input" value="{{ $a->qte_stock }}">
                                @error('stock')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div class="col form-group">
                                <label for="instance">Qte_Instance:</label>
                                <input type="number" class="form-control input-full" id="instance" name="instance" placeholder="Enter Input" value="{{ $a->qte_instance }}">
                                @error('instance')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col form-group">
                                <label for="reserve">Qte_Reserve:</label>
                                <input type="number" class="form-control input-full" id="reserve" name="reserve" placeholder="Enter Input" value="{{ $a->qte_reserve }}">
                                @error('reserve')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div class="col form-group">
                                <label for="prix">prix:</label>
                                <input type="text" class="form-control input-full" id="prix" name="prix" placeholder="Enter Input" value="{{ $a->prix }}">
                                @error('prix')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                        {{-- <div class="row">
                            <div class="col form-group">
                                <label for="photo">photo:</label>
                                <input type="file" class="form-control-file" id="photo" name="photo">
                                @error('photo')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div> --}}
                        <div class="row">
                            <div class="col form-group">
                                <label for="description">Description:</label>
                                <textarea class="form-control" id="description" name="description" rows="5">{{ $a->description }}</textarea>
                                @error('description')
                                <p class="text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>

                        <input type="hidden" value="{{ $a->id }}" name="id_article">

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
