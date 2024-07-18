@include('inc.guest.header')


    <!-- Breadcrumb Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <nav class="breadcrumb bg-light mb-30">
                    <a class="breadcrumb-item text-dark" href="#">Home</a>
                    <a class="breadcrumb-item text-dark" href="#">Shop</a>
                    <span class="breadcrumb-item active">Shop Detail</span>
                </nav>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->


    <!-- Shop Detail Start -->
    <div class="container-fluid pb-5">
        <div class="row px-xl-5">
            <div class="col-lg-5 mb-30">
                <div id="product-carousel" class="carousel slide" data-ride="carousel">

                    <div class="carousel-inner bg-light">
                        <div class="carousel-item active">
                            <img class="w-100 h-100" src="{{ asset('build/assets/img/' . $article->ref . '.jpg') }}" alt="Image">
                        </div>
                    </div>
                    {{-- <a class="carousel-control-prev" href="#product-carousel" data-slide="prev">
                        <i class="fa fa-2x fa-angle-left text-dark"></i>
                    </a>
                    <a class="carousel-control-next" href="#product-carousel" data-slide="next">
                        <i class="fa fa-2x fa-angle-right text-dark"></i>
                    </a> --}}
                </div>
            </div>

            <div class="col-lg-7 h-auto mb-30">
                <div class="h-100 bg-light p-30">
                    <h3>{{$article->ref}}</h3>
                    <p>{{$article->designation}}</p>
                    @if ($article->qte_stock != 0 && $article->qte_instance == 0)
                        <p class="text-success">En Stock</p>
                    @elseif ($article->qte_stock == 0 && $article->qte_instance != 0)
                        <p class="text-warning">En Arrivage</p>
                    @elseif($article->qte_stock == 0 && $article->qte_instance == 0)
                        <p class="text-danger">Rupture de Stock</p>
                    @endif

                    {{-- <form action="{{route('commande.store')}}" method="POST">
                    <div class="d-flex align-items-center mb-4 pt-2">

                            @csrf
                            <input type="hidden" value="{{$article->id}}" name="idarticle">
                        <div class="input-group quantity mr-3" style="width: 130px;">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-primary btn-minus">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                            <input type="text" class="form-control bg-secondary border-0 text-center" value="1" name="qte">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-primary btn-plus">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary px-3"><i class="fa fa-shopping-cart mr-1"></i> Add To Cart</button> --}}

                    </div>
                </form>
                </div>
            </div>
        </div>
        <div class="row px-xl-5">
            <div class="col">
                <div class="bg-light p-30">
                    <div class="nav nav-tabs mb-4">
                        <a class="nav-item nav-link text-dark active" data-toggle="tab" href="#tab-pane-1">Références Origines</a>
                        <a class="nav-item nav-link text-dark" data-toggle="tab" href="#tab-pane-2">Equivalents</a>
                        <a class="nav-item nav-link text-dark" data-toggle="tab" href="#tab-pane-3">Reviews (0)</a>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-pane-1">
                            <div class="container-fluid pt-5">
                                <div class="row px-xl-5 pb-3">
                                    @foreach ($origines as $o)
                                    <div class="col-lg-3 col-md-4 col-sm-6 pb-1">
                                        <a class="text-decoration-none" href="">
                                            <div class="cat-item d-flex align-items-center mb-4">
                                                {{--
                                                <div class="overflow-hidden" style="width: 100px; height: 100px;">
                                                    <img class="img-fluid" src="{{ asset('build/assets/img/' . $e->ref . '.jpg') }}" alt="image">
                                                </div>
                                                 --}}
                                                <div class="flex-fill pl-3">
                                                    <h6>{{$o->ref_O}}</h6>
                                                    <small class="text-body">{{$o->marque}}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="row tab-pane fade" id="tab-pane-2">
                            <div class="container-fluid pt-5">
                                <div class="row px-xl-5 pb-3">
                                    @foreach ($equivalent as $e)
                                    <div class="col-lg-3 col-md-4 col-sm-6 pb-1">
                                        <a class="text-decoration-none" href="">
                                            <div class="cat-item d-flex align-items-center mb-4">
                                                {{--
                                                <div class="overflow-hidden" style="width: 100px; height: 100px;">
                                                    <img class="img-fluid" src="{{ asset('build/assets/img/' . $e->ref . '.jpg') }}" alt="image">
                                                </div>
                                            --}}
                                                <div class="flex-fill pl-3">
                                                    <h6>{{$e->ref}}</h6>
                                                    <small class="text-body">{{$e->marque}}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-pane-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="mb-4">1 review for "Product Name"</h4>
                                    <div class="media mb-4">
                                        <img src="img/user.jpg" alt="Image" class="img-fluid mr-3 mt-1" style="width: 45px;">
                                        <div class="media-body">
                                            <h6>John Doe<small> - <i>01 Jan 2045</i></small></h6>
                                            <div class="text-primary mb-2">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star-half-alt"></i>
                                                <i class="far fa-star"></i>
                                            </div>
                                            <p>Diam amet duo labore stet elitr ea clita ipsum, tempor labore accusam ipsum et no at. Kasd diam tempor rebum magna dolores sed sed eirmod ipsum.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h4 class="mb-4">Leave a review</h4>
                                    <small>Your email address will not be published. Required fields are marked *</small>
                                    <div class="d-flex my-3">
                                        <p class="mb-0 mr-2">Your Rating * :</p>
                                        <div class="text-primary">
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                        </div>
                                    </div>
                                    <form>
                                        <div class="form-group">
                                            <label for="message">Your Review *</label>
                                            <textarea id="message" cols="30" rows="5" class="form-control"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="name">Your Name *</label>
                                            <input type="text" class="form-control" id="name">
                                        </div>
                                        <div class="form-group">
                                            <label for="email">Your Email *</label>
                                            <input type="email" class="form-control" id="email">
                                        </div>
                                        <div class="form-group mb-0">
                                            <input type="submit" value="Leave Your Review" class="btn btn-primary px-3">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Shop Detail End -->


    <!-- Footer Start -->
    @include('inc.client.footer')
