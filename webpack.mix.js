const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 */

// ÁREA ADMINISTRATIVA
mix.js('resources/js/app.js', 'public/js')
   .postCss('resources/css/app.css', 'public/css');

// ÁREA PÚBLICA
mix.js('resources/js/public.js', 'public/js')
   .vue({ version: 3 })
   .postCss('resources/css/public.css', 'public/css', [
       require('tailwindcss'),
       require('autoprefixer'),
   ])
   .alias({
       '@': 'resources/js'
   });

// Versioning
if (mix.inProduction()) {
    mix.version();
}