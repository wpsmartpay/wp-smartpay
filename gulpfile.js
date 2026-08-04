const { src, dest, series } = require('gulp')
const fs = require('fs')
const del = require('del')
const zip = require('gulp-zip')
const { execSync } = require('child_process')

const removeTemp = (cb) => {
    del.sync(['releases/'])
    cb()
}

const composerProd = (cb) => {
    execSync('composer install --no-dev --no-ansi --no-cache --no-interaction', { stdio: 'inherit' })
    cb()
}

const copy = () => {
    return src([
        './**',
        './*/**',
        '!./resources/blocks/**',
        '!./resources/form-builder/**',
        '!./resources/sass/**',
        '!./resources/js/**',
        '!./node_modules/**',
        '!./scripts/**',
        '!./releases/**',
        '!./test-results/**',
        '!./docs/**',
        '!gulpfile.js',
        '!CLAUDE.md',
        '!UI_GUIDELINES.md',
        '!phpcs-bootstrap.php',
        '!webpack.mix.js',
        '!*.json',
        '!*.yml',
        '!*.xml',
        '!*.zip',
        '!*.config.js',
        '!*.lock',
        '!*.log',
        '!*.gitignore',
        '!.DS_Store',
        '!.prettierrc',
        '!.wp-env.json',
        '!phpcs.xml.dist',
        '!.claude/**',
        'composer.json',
    ]).pipe(dest('releases/smartpay'))
}

const getPluginVersion = () => {
    let text = fs.readFileSync('smartpay.php', 'utf-8')

    let match = /(?<=Version:\s*)(\d+(\.\d+)?(\.\d+)?(\.\d+)?)(?:-[A-Za-z]+)?(?![\d.])(?:-\d+)?/.exec(
        text
    )

    if (Array.isArray(match)) {
        return match[0]
    }

    return
}

const bundle = () => {
    let version = getPluginVersion()

    if (!version) {
        notify.onError("Can't find version number!\n")
        return
    }

    return src(['./releases/**', './releases/*/**'])
        .pipe(zip(`smartpay-${version}.zip`))
        .pipe(dest('./releases'))
}

exports.release = series(removeTemp, composerProd, copy, bundle)
// exports.default = build
