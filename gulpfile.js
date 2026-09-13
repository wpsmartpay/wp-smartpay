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

// Composer dev requirements — must never reach the shipped package.
const DEV_VENDORS = [
    'squizlabs',
    'wp-coding-standards',
    'phpcompatibility',
    'phpcsstandards',
    'dealerdirect',
]

/**
 * Verify the built package carries no composer dev dependencies.
 *
 * `composerProd` should guarantee this, but vendor/ is copied verbatim — a
 * stale tree from an earlier `composer install` would ship silently. Fail the
 * build instead of trusting the previous step.
 */
const verify = (cb) => {
    const pkg = 'releases/smartpay'
    const problems = []

    DEV_VENDORS.forEach((name) => {
        if (fs.existsSync(`${pkg}/vendor/${name}`)) {
            problems.push(`dev dependency shipped: vendor/${name}`)
        }
    })

    if (fs.existsSync(`${pkg}/vendor/bin`)) {
        problems.push('dev dependency shipped: vendor/bin (phpcs/phpcbf shims)')
    }

    const installed = `${pkg}/vendor/composer/installed.json`
    if (fs.existsSync(installed)) {
        const meta = JSON.parse(fs.readFileSync(installed, 'utf-8'))
        if (meta.dev !== false) {
            problems.push('vendor/composer/installed.json reports dev packages installed')
        }
    }

    if (problems.length) {
        cb(new Error(`Package verification failed:\n  - ${problems.join('\n  - ')}`))
        return
    }

    console.log('[+] Package verified: no composer dev dependencies')
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

exports.release = series(removeTemp, composerProd, copy, verify, bundle)
// Exposed so the built package can be re-checked without a full rebuild.
exports.verify = verify
// exports.default = build
