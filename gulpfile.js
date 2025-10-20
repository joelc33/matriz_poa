var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

/*elixir(function(mix) {
    mix.sass('app.scss');
});*/

elixir(function(mix) {
    mix.styles([
        "normalize.css",
        "ext-all.css",
        "iconos.css",
        "combos.css",
        "fileuploadfield.css"
    ], 'public/css/app.css', 'public/css');

    mix.scripts([
        "ext-base.js",
        "ext-all.js",
        "ext-lang-es.js",
        "ux-all.js",
        "funciones_comunes/paqueteComun.js",
        "open/js/swfobject.js",
        "util.js",
        "funciones_comunes/Ext.util.JSON.js",
        "funciones_comunes/SuperBoxSelect.js",
        "funciones_comunes/CMS.view.FileDownload.js"
    ], 'public/js/app.js', 'public/js');

});
