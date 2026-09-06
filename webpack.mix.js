const mix = require('laravel-mix');
const path = require('path');

mix.webpackConfig({
    watchOptions: {
        ignored: /node_modules|public|mix-manifest\.json$/,
    },
    externals: {
        react: 'React',
        'react-dom': 'ReactDOM',
        jquery: 'jQuery',
        $: 'jQuery',
        '@wordpress/element':    'wp.element',
        '@wordpress/data':       'wp.data',
        '@wordpress/components': 'wp.components',
        '@wordpress/i18n':       'wp.i18n',
        '@wordpress/plugins':    'wp.plugins',
        '@wordpress/edit-post':  'wp.editPost',
        '@wordpress/api-fetch':  'wp.apiFetch',
        '@wordpress/hooks':      'wp.hooks',
        '@wordpress/core-data':  'wp.coreData',
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
    .js('resources/js/components/index.js', 'public/js/ui.js')
    .js('resources/js/admin/form-editor-sidebar/index.js', 'public/js/admin/form-editor-sidebar.js')
    .css('resources/css/admin/form-editor-sidebar.css', 'public/css/admin/form-editor-sidebar.css')
    .js('resources/js/pages/support/index.js', 'public/js/support.js')
    .js('resources/js/frontend/login.js', 'public/js/frontend/login.js')
    .css('resources/css/frontend/login.css', 'public/css/frontend/login.css')
    .js('resources/js/frontend/profile.js', 'public/js/frontend/profile.js')
    .css('resources/css/frontend/profile.css', 'public/css/frontend/profile.css')
    .js('resources/js/frontend/registration.js', 'public/js/frontend/registration.js')
    .css('resources/css/frontend/registration.css', 'public/css/frontend/registration.css')
    .css('resources/css/frontend/dashboard.css', 'public/css/frontend/dashboard.css')
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
