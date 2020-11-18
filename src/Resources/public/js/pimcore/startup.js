pimcore.registerNS("pimcore.plugin.ObjectDeleterBundle");

pimcore.plugin.ObjectDeleterBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.ObjectDeleterBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        // alert("ObjectDeleterBundle ready!");
    }
});

var ObjectDeleterBundlePlugin = new pimcore.plugin.ObjectDeleterBundle();
