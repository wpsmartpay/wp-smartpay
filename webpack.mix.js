const mix = require('laravel-mix');
const path = require('path');

mix.webpackConfig({
    externals: {
        react: 'React',
        'react-dom': 'ReactDOM',
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            '@/components': path.resolve(__dirname, './resources/js/components'),
            '@/lib': path.resolve(__dirname, './resources/js/lib'),
        },
        extensions: ['.js', '.jsx', '.json', '.mjs'],
        fullySpecified: false,
    },
    module: {
        rules: [
            {
                test: /\.m?js$/,
                resolve: {
                    fullySpecified: false,
                },
            },
            {
                test: /\.mjs$/,
                include: /node_modules/,
                type: 'javascript/auto',
            },
        ],
    },
})
    .js('resources/js/frontend/bootstrap.js', 'public/js')
    .js('resources/js/admin/integration.js', 'public/js')
    .js('resources/js/admin/debuglog.js', 'public/js')
    .js('resources/js/admin/dashboard.js', 'public/js')
    .js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .js('resources/js/admin.js', 'public/js')
    .react()
    .sass('resources/sass/admin.scss', 'public/css')
    .sass('resources/sass/dashboard.scss', 'public/css')
    .postCss('resources/css/components.css', 'public/css', [
        require('@tailwindcss/postcss'),
        require('autoprefixer'),
    ])
    .sourceMaps(false)
    .options({
        processCssUrls: false,
        terser: {
            extractComments: false,
        },
    });
