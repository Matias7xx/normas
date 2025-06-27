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

// Configurações para desenvolvimento
if (!mix.inProduction()) {
    // Habilitar source maps para debug
    mix.sourceMaps();
    
    // Configuração do BrowserSync para hot reload
    mix.browserSync({
        proxy: 'localhost', // URL do nginx
        files: [
            'resources/views/**/*.php',
            'resources/js/**/*.js',
            'resources/js/**/*.vue', 
            'resources/css/**/*.css',
            'app/**/*.php',
            'routes/**/*.php'
        ],
        watchOptions: {
            usePolling: true,
            interval: 300
        }
    });
}

// Versioning apenas em produção
if (mix.inProduction()) {
    mix.version();
}

// Configurações do webpack
mix.webpackConfig({
    watchOptions: {
        ignored: /node_modules/,
        poll: 1000,
    }
});