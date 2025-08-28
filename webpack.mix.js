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

// Configurações para desenvolvimento LOCAL - SEM Docker
if (!mix.inProduction()) {
    // Source maps para debug
    mix.sourceMaps();
    
    // Configuração 
    mix.options({
        hmrOptions: {
            host: 'localhost',
            port: 8080
        }
    });
    
    // REMOVER browserSync - não precisa com php artisan serve
    // mix.browserSync()
}

// Versioning apenas em produção
if (mix.inProduction()) {
    mix.version();
}

// Configurações do webpack
mix.webpackConfig({
    stats: {
        children: true,
    }
});