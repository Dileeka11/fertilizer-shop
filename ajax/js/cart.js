/**
 * Storefront cart helpers. Talks to ajax/php/cart.php.
 */
(function (global) {
    const ENDPOINT = '/fertilizer-shop/ajax/php/cart.php';

    function call(action, data) {
        const body = new URLSearchParams(Object.assign({ action: action }, data || {}));
        return fetch(ENDPOINT, {
            method: 'POST',
            credentials: 'same-origin',
            body: body
        }).then(function (r) { return r.json(); });
    }

    function updateBadge(count) {
        const el = document.getElementById('cart-count');
        if (el) el.textContent = count;
    }

    global.AgroCart = {
        add: function (productNo, qty) {
            return call('add', { product_no: productNo, qty: qty || 1 })
                .then(function (res) { if (res.ok) updateBadge(res.count); return res; });
        },
        update: function (productNo, qty) {
            return call('update', { product_no: productNo, qty: qty })
                .then(function (res) { if (res.ok) updateBadge(res.count); return res; });
        },
        remove: function (productNo) {
            return call('remove', { product_no: productNo })
                .then(function (res) { if (res.ok) updateBadge(res.count); return res; });
        },
        clear: function () {
            return call('clear').then(function (res) { if (res.ok) updateBadge(res.count); return res; });
        },
        get: function () { return call('get'); }
    };
})(window);
