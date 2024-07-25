  <!-- Footer Start -->
  <div class="container-fluid bg-dark justify-content-center text-secondary mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-12">
            <div class="row px-xl-5 pt-5">
                <h5 class="text-primary text-uppercase mb-4">Contactez nous</h5>
            </div>
            <div class="row px-xl-5 pt-5">
                <div class="col-lg-8 col-md-12 mb-5 pr-3 pr-xl-5">
                    <a class="mb-2" href="https://www.google.com/maps/place/Etablissement+REKIK+HABIB/@36.7859002,10.078516,17z/data=!3m1!4b1!4m6!3m5!1s0x12fd2f41c9c4ee0d:0xda7c17b6d4864637!8m2!3d36.7859002!4d10.0810909!16s%2Fg%2F11fn50sxct?authuser=0&entry=ttu" target="_blank">
                        <i class="fa fa-map-marker-alt text-primary mr-3"></i>
                        Rond point Ben Mahmoud Résidence Boubli , Lot 86 , 1082 EL Agba Tunis
                    </a>
                    <br>
                    <a class="mb-2" href="mailto:contact@etablissement-rekik.com" target="_blank"><i class="fa fa-envelope text-primary mr-3"></i>contact@etablissement-rekik.com</a>
                    <p class="mb-2"><i class="fa fa-phone-alt text-primary mr-3"></i>(+216) 54 790 790</p>
                    <p class="mb-2"><i class="fa fa-phone-alt text-primary mr-3"></i>(+216) 58 534 535</p>
                    <a href="https://www.facebook.com/profile.php?id=61555440732992" target="_blank"><i class="fab fa-facebook-f text-primary mr-3"></i>Etablissement REKIK</a>
                </div>
                <div class="col-lg-4 col-md-12 mb-5 pr-3 pr-xl-5">
                    <h7>Service Gros:</h7><p class="mb-2">(+216) 58 534 535</p>
                    <h7>Service Details:</h7><p class="mb-2">(+216) 56 278 776</p>
                    <h7>Fax:</h7><p class="mb-2">(+216) 32 404 424</p>
                </div>
            </div>
        </div>
        <div class="col col-lg-4 col-md-12">
                <img src="{{asset('build/dashassets/img/tes.jpg')}}"/>
        </div>
    </div>
    <div class="row border-top mx-xl-5 py-4" style="border-color: rgba(256, 256, 256, .1) !important; ">
        <div class="col-md px-xl-0">
            <p class="text-center text-primary">
                &copy; 2024 – Ets. REKIK Habib – tous les droits sont réservés.
            </p>
        </div>
    </div>
</div>
<!-- Footer End -->


<!-- Back to Top -->
<a href="#" class="btn btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>


<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
<script src="{{asset('lib/easing/easing.min.js')}}"></script>
<script src="{{asset('lib/owlcarousel/owl.carousel.min.js')}}"></script>

<!-- Contact Javascript File -->
<script src="{{asset('mail/jqBootstrapValidation.min.js')}}"></script>
<script src="{{asset('mail/contact.js')}}"></script>

<!-- Template Javascript -->
<script src="{{asset('js/main.js')}}"></script>

<script>
    $(document).ready(function() {
      $('.btn-minus').click(function (e) {
        e.preventDefault();
        var $qty = $(this).closest('.quantity').find('input[name="qte"]');
        var currentVal = parseInt($qty.val());
        if (currentVal > 1) {
          $qty.val(currentVal - 1);
        }
      });
      $('.btn-plus').click(function (e) {
        e.preventDefault();
        var $qty = $(this).closest('.quantity').find('input[name="qte"]');
        var currentVal = parseInt($qty.val());
        $qty.val(currentVal + 1);
      });
    });
</script>
</body>

</html>
