/* Storefront helper — keeps the cart badge in sync with the server. */
(function () {
    if (window.AgroCart && typeof window.AgroCart.get === 'function') {
        window.AgroCart.get().then(function (res) {
            if (res && res.ok) {
                var el = document.getElementById('cart-count');
                if (el) el.textContent = res.count;
            }
        }).catch(function () { /* ignore */ });
    }
})();
