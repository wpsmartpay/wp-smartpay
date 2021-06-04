const mix = require('laravel-mix')

mix.webpackConfig({
    externals: {
        react: 'React',
        'react-dom': 'ReactDOM',
    },
})
    .copy('resources/js/frontend/bootstrap.js', 'public/js')
    .copy('resources/js/admin/integration.js', 'public/js')
    .copy('resources/js/admin/debuglog.js', 'public/js')
    .js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .js('resources/js/admin.js', 'public/js')
    .sass('resources/sass/admin.scss', 'public/css')
    .sourceMaps(false)
