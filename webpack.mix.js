const mix = require('laravel-mix')

mix.js('resources/js/admin.js', 'public/js')
    .js('resources/js/block-editor/block-editor.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/block-editor/block-editor.scss', 'public/css')
    .sass('resources/sass/admin.scss', 'public/css')
    .sourceMaps(false)
