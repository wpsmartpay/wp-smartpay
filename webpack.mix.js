const mix = require('laravel-mix')

mix.webpackConfig({
    externals: {
        react: 'React',
        'react-dom': 'ReactDOM',
    },
})
    .js('resources/js/admin.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/block-editor/block-editor.scss', 'public/css')
    .sass('resources/sass/admin.scss', 'public/css')
    .sourceMaps(false)
