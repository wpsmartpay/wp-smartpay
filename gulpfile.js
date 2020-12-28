const { src, dest, series } = require('gulp')
const fs = require('fs')
const del = require('del')
const zip = require('gulp-zip')

const removeTemp = (cb) => {
    del.sync(['temp/'])

    cb()
}

const copy = (cb) => {
    src([
        './**',
        './*/**',
        '!./resources/blocks/**',
        '!./resources/form-builder/**',
        '!./resources/sass/**',
        '!./resources/js/**',
        '!./node_modules/**',
        '!./scripts/**',
        '!./temp/**',
        '!gulpfile.js',
        '!webpack.mix.js',
        '!*.json',
        '!*.yml',
        '!*.xml',
        '!*.zip',
        '!*.config.js',
        '!*.lock',
        '!*.log',
        '!*.gitignore',
    ]).pipe(dest('temp/smartpay'))

    cb()
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

const bundle = (cb) => {
    let version = getPluginVersion()

    if (!version) {
        notify.onError("Can't find version number!\n")
        return
    }

    src(['./temp/**', './temp/*/**'])
        .pipe(zip(`smartpay-${version}.zip`))
        .pipe(dest('./temp'))

    cb()
}

exports.release = series(removeTemp, copy, bundle)
// exports.default = build
