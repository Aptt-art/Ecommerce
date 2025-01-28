<nav class="bg-white/70 backdrop-blur-md py-3 px-6 shadow-lg flex justify-between items-center">
    <a href="index.php" class="text-xl font-bold text-black">TechShop</a>
    <div class="flex gap-4">
        <a href="index.php" class="text-gray-800 hover:underline">Accueil</a>
        <a href="cart.php" class="text-green-600 hover:underline relative">
            Panier 🛒
            <span id="cart-notification" class="absolute top-0 right-0 bg-green-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full"></span>
        </a>
    </div>
</nav>

<div id="promo-banner" class="overflow-hidden bg-green-600 text-white py-2">
    <div id="promo-text" class="whitespace-nowrap text-sm font-semibold flex gap-8">
        <span>🎉 Livraison gratuite à partir de 50€ d'achat !</span>
        <span>-30% sur les Smartphones jusqu'à dimanche !</span>
        <span>Offre spéciale : Achetez 2 produits, le 3ème à moitié prix !</span>
    </div>
</div>

<script>
    const promoText = document.getElementById("promo-text");
    let scrollAmount = 0;

    function scrollPromo() {
        scrollAmount--;
        promoText.style.transform = `translateX(${scrollAmount}px)`;
        if (Math.abs(scrollAmount) >= promoText.offsetWidth) {
            scrollAmount = promoText.parentElement.offsetWidth;
        }
        requestAnimationFrame(scrollPromo);
    }

    scrollPromo();
</script>
