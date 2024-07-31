<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Etablissement Rekik</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="{{ asset('build/assets/img/007.ico') }}" type="image/x-icon" />
    <!-- Fonts and icons -->

    <script src="{{ asset('build/dashassets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            google: {
                families: ["Public Sans:300,400,500,600,700"]
            },
            custom: {
                families: [
                    "Font Awesome 5 Solid",
                    "Font Awesome 5 Regular",
                    "Font Awesome 5 Brands",
                    "simple-line-icons",
                ],
                urls: ["{{ asset('build/dashassets/css/fonts.min.css') }}"],
            },
            active: function() {
                sessionStorage.fonts = true;
            },
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('build/dashassets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('build/dashassets/css/plugins.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('build/dashassets/css/kaiadmin.min.css') }}" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="{{ asset('build/dashassets/css/demo.css') }}" />
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
                            <img src="{{ asset('build/dashassets/img/kaiadmin/logo_light.svg') }}" alt="navbar brand"
                                class="navbar-brand" height="20" />
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
                    {{-- <div
                  class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4"
                > --}}
                    <div class="row">
                        <div class="col-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#importE">
                                <i class="fa fa-file-import"></i> Equival
                            </button>
                        </div>

                        <div class="col-2">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#import">
                                <i class="fa fa-file-import"></i> Articles
                            </button>
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#importO">
                                <i class="fa fa-file-import"></i> Origin
                            </button>
                        </div>
                        <div class="col-5" style="margin-right:0cm">
                            <form action="{{ route('articles.recherche') }}" method="GET">
                                @csrf
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Rechercher..."
                                        name="q">
                                    <span class="input-group-btn">
                                        <button class="btn btn-warning btn-border" type="submit">
                                            <i class="fa fa-search search-icon"></i>
                                        </button>
                                    </span>
                                </div>
                            </form>
                        </div>

                    </div>
                    {{-- </div> --}}


                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-head-bg table-bordered mt-4">
                            <thead display:block;>
                                <tr>

                                </tr>
                                <tr>
                                    <th scope="col" style="background-color: #ffce47;" rowspan="2">#</th>
                                    <th scope="col" style="background-color: #ffce47;" rowspan="2">Réference</th>
                                    <th scope="col" style="background-color: #ffce47;" rowspan="2">Déscription
                                    </th>
                                    <th scope="col" style="background-color: #ffce47;" rowspan="2">Prix</th>
                                    <th scope="col" style=" background-color: #ffce47; text-align: center;"
                                        colspan="3">Stock</th>
                                    <th scope="col" style="background-color: #ffce47;" rowspan="2">Action</th>
                                </tr>
                                <tr>

                                    <th scope="col" style="background-color: #ffce47;">Disp.</th>
                                    <th scope="col" style="background-color: #ffce47;">Inst.</th>
                                    <th scope="col" style="background-color: #ffce47;">Res.</th>

                                </tr>
                            </thead>
                            <tbody overflow-y: scroll>
                                @foreach ($articles as $index => $a)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div>
                                                <img src="{{ asset('build/assets/img/' . $a->ref . '.jpg') }}"
                                                    height="40" />
                                                <br>
                                                <a>
                                                    {{ $a->ref }}
                                                    <a>

                                            </div>
                                        </td>
                                        <td>{{ $a->designation }} <br> {{ $a->description }}</td>
                                        <td align="right">{{ $a->getPrice() }}</td>
                                        <td align="right" style="background-color: lightgreen; color: black;">
                                            {{ $a->qte_stock }}</td>
                                        <td align="right" style="background-color: lightblue; color: black;">
                                            {{ $a->qte_instance }}</td>
                                        <td align="right" style="background-color: IndianRed; color: black;">
                                            {{ $a->qte_reserve }}</td>


                                        <td>
                                            <div class="d-flex">
                                                <a data-bs-toggle="modal"
                                                    data-bs-target="#editArticle{{ $a->id }}"
                                                    class="btn btn-link btn-primary btn-lg"
                                                    data-original-title="Edit Task">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a onclick="return confirm('Voulez-vous vraiment supprimer cet article?')"
                                                    href="{{ route('articles.delete', ['id' => $a->id]) }}"
                                                    class="btn btn-link btn-danger">
                                                    <i class="fa fa-times"></i>
                                                </a>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" data-id="{{ $a->id }}" {{ $a->sel == '1' ? 'checked' : '' }} value="1">
                                                    {{-- <label class="form-check-label" for="flexSwitchCheckDefault">Default switch checkbox input</label> --}}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
                <div>
                    {{ $articles->links('pagination::bootstrap-5') }}
                </div>
            </div>


        </div>

    </div>


    <!-- Modal import -->
  <div class="modal fade" id="import" tabindex="-1" aria-labelledby="Articles" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Importer un fichier</span></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                {{-- <form id="csvForm" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="csv_file">Choose CSV file</label>
                <input type="file" class="form-control-file" id="csv_file" name="csv_file" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>

        <div class="mt-3">
            <h3>Progress: <span id="progress">0%</span></h3>
        </div> --}}
                <form action="{{route('articles.import')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col form-group">
                                <div class="mb-3">
                                    <label for="csvFile" class="form-label">Importer un fichier</label>
                                    <input class="form-control" type="file" id="csvFile" name="csv_file"  accept=".csv">
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-border" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Importer</button>
                    </div>
                </form>
                <div class="progress mt-3" style="height: 30px;">
                    <div id="progress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Import Equivalents -->
    <div class="modal fade" id="importE" tabindex="-1" aria-labelledby="Equivalents" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Importer un fichier</span></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('equi.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col form-group">
                                <div class="mb-3">
                                    <label for="prefix" class="form-label">Choisir un préfix</label>
                                    <input type="text" class="form-control input-full" id="prefix" name="prefix" placeholder="Enter Input">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col form-group">
                                <div class="mb-3">
                                    <label for="import_csv1" class="form-label">Importer un fichier</label>
                                    <input class="form-control" type="file" id="import_csv1" name="import_csv1" accept=".csv">
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-border"
                                data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Importer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Import Origin -->
    <div class="modal fade" id="importO" tabindex="-1" aria-labelledby="Origins" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Importer un fichier</span></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('origin.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col form-group">
                                <div class="mb-3">
                                    <label for="prefix" class="form-label">Choisir un préfix</label>
                                    <input type="text" class="form-control input-full" id="prefix" name="prefix" placeholder="Enter Input">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col form-group">
                                <div class="mb-3">
                                    <label for="import_csv2" class="form-label">Importer un fichier</label>
                                    <input class="form-control" type="file" id="import_csv2" name="import_csv2" accept=".csv">
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-border" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Importer</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
    @foreach ($articles as $index => $a)
        <!-- Modal Modifier -->
        <div class="modal fade" id="editArticle{{ $a->id }}" tabindex="-1"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Modifier l'article <span
                                class="text-primary">{{ $a->ref }}</span></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form action="{{ route('articles.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col form-group">
                                    <label for="ref">Réf:</label>
                                    <input type="text" class="form-control input-full" id="ref"
                                        name="ref" value="{{ $a->ref }}">
                                    @error('ref')
                                        <p class="text-danger">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                <div class="col form-group">
                                    <label for="designation">Désignation:</label>
                                    <input type="text" class="form-control input-full" id="designation"
                                        name="designation" placeholder="Enter Input" value="{{ $a->designation }}">
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
                                    <input type="number" class="form-control input-full" id="stock"
                                        name="stock" placeholder="Enter Input" value="{{ $a->qte_stock }}">
                                    @error('stock')
                                        <p class="text-danger">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                <div class="col form-group">
                                    <label for="instance">Qte_Instance:</label>
                                    <input type="number" class="form-control input-full" id="instance"
                                        name="instance" placeholder="Enter Input" value="{{ $a->qte_instance }}">
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
                                    <input type="number" class="form-control input-full" id="reserve"
                                        name="reserve" placeholder="Enter Input" value="{{ $a->qte_reserve }}">
                                    @error('reserve')
                                        <p class="text-danger">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                <div class="col form-group">
                                    <label for="prix">prix:</label>
                                    <input type="text" class="form-control input-full" id="prix"
                                        name="prix" placeholder="Enter Input" value="{{ $a->prix }}">
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
                            <button type="button" class="btn btn-danger btn-border"
                                data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Modifier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <!--   Core JS Files   -->
    <script src="{{ asset('build/dashassets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('build/dashassets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('build/dashassets/js/core/bootstrap.min.js') }}"></script>

    <!-- jQuery Scrollbar -->
    <script src="{{ asset('build/dashassets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>

    <!-- Chart JS -->
    <script src="{{ asset('build/dashassets/js/plugin/chart.js/chart.min.js') }}"></script>

    <!-- jQuery Sparkline -->
    <script src="{{ asset('build/dashassets/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}"></script>

    <!-- Chart Circle -->
    <script src="{{ asset('build/dashassets/js/plugin/chart-circle/circles.min.js') }}"></script>

    <!-- Datatables -->
    <script src="{{ asset('build/dashassets/js/plugin/datatables/datatables.min.js') }}"></script>

    <!-- Bootstrap Notify -->
    <script src="{{ asset('build/dashassets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <!-- jQuery Vector Maps -->
    <script src="{{ asset('build/dashassets/js/plugin/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('build/dashassets/js/plugin/jsvectormap/world.js') }}"></script>

    <!-- Sweet Alert -->
    <script src="{{ asset('build/dashassets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>

    <!-- Kaiadmin JS -->
    <script src="{{ asset('build/dashassets/js/kaiadmin.min.js') }}"></script>

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.form-check-input').change(function() {
                var selValue = $(this).is(':checked') ? 1 : 0;
                var id = $(this).data('id');

                $.ajax({
                    url: '{{ route("updateSel") }}', // Define this route in your routes file
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        id: id,
                        sel: selValue
                    },
                    success: function(response) {
                        console.log('Switch value updated successfully');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating switch value:', error);
                    }
                });
            });
        });
    </script>


</body>

</html>
