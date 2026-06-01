/**
 * Category CRUD AJAX helpers.
 */
(function (global) {
    const ENDPOINT = '/fertilizer-shop/ajax/php/categories.php';
    function call(action, data) {
        const body = new URLSearchParams(Object.assign({ action: action }, data || {}));
        return fetch(ENDPOINT, { method: 'POST', credentials: 'same-origin', body: body })
                   .then(function (r) { return r.json(); });
    }
    global.AgroCategories = {
        list:   function ()       { return call('list'); },
        get:    function (id)     { return call('get',    { category_id: id }); },
        create: function (d)      { return call('create', d); },
        update: function (id, d)  { return call('update', Object.assign({ category_id: id }, d)); },
        remove: function (id)     { return call('delete', { category_id: id }); }
    };
})(window);
