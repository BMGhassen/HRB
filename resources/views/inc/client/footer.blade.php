    {{-- Modern Dark Footer --}}
    <footer class="bg-lion-ink border-t border-zinc-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div>
                    <h5 class="text-lion-accent font-black uppercase tracking-wider mb-4">Établissement Rekik</h5>
                    <p class="text-zinc-400 text-sm leading-relaxed mb-4">
                        Distributeur officiel de pièces automobiles en gros. Qualité, stock et service rapide.
                    </p>
                </div>
                <div>
                    <h5 class="text-white font-bold mb-4">Contactez-nous</h5>
                    <ul class="space-y-3 text-sm text-zinc-400">
                        <li>
                            <a href="https://www.google.com/maps/place/Etablissement+REKIK+HABIB/@36.7859002,10.078516,17z" target="_blank" class="hover:text-lion-accent transition-colors flex items-start gap-2">
                                <i class="fa fa-map-marker-alt text-lion-accent mt-1"></i>
                                <span>Rond point Ben Mahmoud Résidence Boubli, Lot 86, 1082 EL Agba Tunis</span>
                            </a>
                        </li>
                        <li>
                            <a href="mailto:contact@etablissement-rekik.com" class="hover:text-lion-accent transition-colors flex items-center gap-2">
                                <i class="fa fa-envelope text-lion-accent"></i> contact@etablissement-rekik.com
                            </a>
                        </li>
                        <li class="flex items-center gap-2"><i class="fa fa-phone-alt text-lion-accent"></i> (+216) 54 790 790</li>
                        <li class="flex items-center gap-2"><i class="fa fa-phone-alt text-lion-accent"></i> (+216) 58 534 535</li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-white font-bold mb-4">Service Gros & Fax</h5>
                    <ul class="space-y-3 text-sm text-zinc-400">
                        <li class="flex items-center gap-2"><i class="fa fa-phone text-lion-accent"></i> Gros : (+216) 58 534 535</li>
                        <li class="flex items-center gap-2"><i class="fa fa-fax text-lion-accent"></i> Fax : (+216) 32 404 424</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-zinc-800 mt-12 pt-8 text-center text-zinc-500 text-xs">
                &copy; {{ date('Y') }} – ABS – Tous droits réservés.
            </div>
        </div>
    </footer>


<!-- jQuery (needed for AJAX in cart) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@if(session()->has('erp_payloads'))
<script>
    console.group('📦 iDempiere ERP Sync Payloads');
    const payloads = @json(session()->pull('erp_payloads'));
    payloads.forEach(p => {
        console.log(`%c[${p.type}]`, 'color: #FFC107; font-weight: bold;', p.data);
    });
    console.groupEnd();
</script>
@endif

</body>
</html>
