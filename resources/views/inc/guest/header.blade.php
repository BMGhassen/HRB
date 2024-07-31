<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Etablissement Rekik</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">
    <link
          rel="icon"
          href="{{asset('build/assets/img/007.ico')}}"
          type="image/x-icon"
        />
    <!-- Favicon -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="{{asset('build/shopassets/lib/animate/animate.min.css')}}" rel="stylesheet">
    <link href="{{asset('build/shopassets/lib/owlcarousel/assets/owl.carousel.min.css')}}" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{asset('build/shopassets/css/style.css')}}" rel="stylesheet">
</head>

<body>
    <!-- Topbar Start -->
    <div class="container-fluid">
        <div class="row align-items-center bg-light py-3 px-xl-5 d-none d-lg-flex">
            <div class="col-lg-6">
                <a href="" class="text-decoration-none">
                    <span class="h1 text-uppercase text-primary bg-dark px-2">Etablissement</span>
                    <span class="h1 text-uppercase text-dark bg-primary px-2 ml-n1">Rekik</span>
                </a>
            </div>
            <div class="col-lg-4 col-6 text-left">
                <div class="card">
                <form action= "{{ route('guest.article.search') }}" method="GET">
                    @csrf
                    <div class="input-group">
                        <input type="text" class="typeahead form-control" placeholder="Rechercher Par Référence" name="search" required>
                        {{-- <ul id="suggestions" class="list-group my-2"></ul> --}}
                        <div class="input-group-append">
                            <button class="input-group-text bg-transparent text-primary" type="submit">

                                <i class="fa fa-search"></i>

                        </button>
                        </div>
                    </div>
                </form>
                </div>
            </div>

        </div>
    </div>
    <!-- Topbar End -->


    <!-- Navbar Start -->
    <div class="container-fluid bg-dark mb-30">
        <div class="row px-xl-5">
            <div class="col-lg-3 d-none d-lg-block">
                <a class="btn d-flex align-items-center justify-content-between bg-primary w-100" data-toggle="collapse" href="#navbar-vertical" style="height: 65px; padding: 0 30px;">
                    <h6 class="text-dark m-0"><i class="fa fa-bars mr-2"></i></h6>
                    <i class="fa fa-angle-down text-dark"></i>
                </a>
            </div>
            <div class="col-lg-9">
                <nav class="navbar navbar-expand-lg bg-dark navbar-dark py-3 py-lg-0 px-0">
                    <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                        <div class="navbar-nav mr-auto py-0">
                            <a href="/" class="nav-item nav-link active">Home</a>
                            <a href="" class="nav-item nav-link">Nos Partenaires</a>
                            <a href="" class="nav-item nav-link">Nos Marques</a>
                            <a href="" class="nav-item nav-link">News</a>
                            <a href="" class="nav-item nav-link">à propos</a>

                            <a href="" class="nav-item nav-link">Contact</a>
                        </div>
                        <div class="navbar-nav ml-auto py-0 d-none d-lg-block">
                            @if (Route::has('login'))

                                    <a href="{{ route('login') }}" class="btn btn-primary btn-border">Se Connecter</a>

                            @endif
                            @if (Route::has('register'))

                                    <a href="{{ route('register') }}" class="btn btn-primary btn-border">S'inscrire</a>

                            @endif


                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar End -->

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-3-typeahead@4.0.2/bootstrap3-typeahead.min.js"></script>





    {{-- <script type="text/javascript">
        var path = "{{ route('autocomplete') }}";
        $('input.typeahead').typeahead({
            source:  function (query, process) {
            return $.get({{ route('autocomplete') }}, { query: query }, function (data) {
                    return process(data);
                });
            },
            matcher: function(item) {
        // Ensure item is a string
        if (typeof item !== 'string') {
            item = item.toString();
        }
        return item.toLowerCase().indexOf(this.query.toLowerCase()) !== -1;
    }
        });
    </script> --}}
