
@include('inc.guest.header', ['searchTerm' => $searchTerm])
<div class="container-fluid pt-5 pb-3">
    <div class="row px-xl-5">

    @if ($results->isEmpty())
        <p>No articles found matching your search criteria.</p>

    @else
        @foreach ($results as $index => $r)

        <div class="col-lg-3 col-md-4 col-sm-6 pb-1">
            <div class="product-item bg-light mb-4">
                <div class="product-img position-relative overflow-hidden">

                    <img class="img-fluid w-100" src="{{ asset('build/assets/img/' . $r->ref . '.jpg') }}" alt="">
                    <div class="product-action">
                        <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-shopping-cart"></i></a>
                        <a class="btn btn-outline-dark btn-square" href=""><i class="far fa-heart"></i></a>
                        <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-sync-alt"></i></a>
                        <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-search"></i></a>
                    </div>
                </div>
                <div class="text-center py-4">
                    <h5></h5>
                    <a class="h6" href="{{ route('guest.details', ['id' => $r->id]) }}">{{$r->ref}}</a>

                    <div class="d-flex align-items-center justify-content-center mt-2">
                       <small>{{$r->designation}}</small>
                    </div>
                </div>
            </div>
        </div>


    @endforeach
    <div class="col-12">
        {{ $results->links('pagination::bootstrap-5') }}
    </div>
    @endif

</div>

</div>
@include('inc.client.footer')
