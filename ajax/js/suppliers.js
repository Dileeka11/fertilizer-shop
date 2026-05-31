/**
 * Supplier CRUD AJAX helpers.
 */
(function (global) {
    const ENDPOINT = '/fertilizer-shop/ajax/php/suppliers.php';
    function call(action, data) {
        const body = new URLSearchParams(Object.assign({ action: action }, data || {}));
        return fetch(ENDPOINT, { method: 'POST', credentials: 'same-origin', body: body })
                   .then(function (r) { return r.json(); });
    }
    global.AgroSuppliers = {
        list:   function ()             { return call('list'); },
        get:    function (n)            { return call('get',    { supplier_no: n }); },
        create: function (d)            { return call('create', d); },
        update: function (n, d)         { return call('update', Object.assign({ supplier_no: n }, d)); },
        remove: function (n)            { return call('delete', { supplier_no: n }); }
    };
})(window);
