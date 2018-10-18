let mix = require('laravel-mix');
var tailwindcss = require('tailwindcss');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.setPublicPath(path.resolve('./'))
    .js('./resources/js/site.js', 'js')
    .less('./resources/less/site.less', 'css')
    .options({
        processCssUrls: false,
        postCss: [tailwindcss('tailwind.js')]
    });
