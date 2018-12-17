'use strict';

import autoprefixer from 'gulp-autoprefixer';
import browserify from 'browserify';
import babelify from 'babelify';
import del from 'delete';
import googleWebFonts from 'gulp-google-webfonts';
import gulp from 'gulp';
import log from 'fancy-log';
import path from 'path';
import pug from 'gulp-pug';
import sass from 'gulp-sass';
import source from 'vinyl-source-stream';
import watchify from 'watchify';

const srcPath = path.join(__dirname, '/src');
const buildPath = path.join(__dirname, '/docroot');

function bundler() {
    let b = browserify({
        entries: `${srcPath}/jsx/app.jsx`,
        extensions: ['.jsx'],
        debug: true,
        fullPaths: true,
        cache: {},
        packageCache: {}
    });

    b.transform('babelify', {
        presets: ['@babel/preset-react'],
        plugins: ['@babel/plugin-proposal-class-properties'],
        ignore: ['bower_components', 'node_modules']
    });

    return b;
}

let tasks = {
    clean: () => {
        return del.promise([`${buildPath}/*`]);
    },
    sass: () => {
        return gulp.src([`${srcPath}/scss/app.scss`])
            .pipe(sass())
            .on('error', sass.logError)
            .pipe(autoprefixer({
                browsers: ['last 2 versions']
            }))
            .pipe(gulp.dest(`${buildPath}/css`));
    },
    pug: () => {
        return gulp.src([`${srcPath}/pug/*`])
            .pipe(pug({
                pretty: true
            }))
            .pipe(gulp.dest(`${buildPath}/`));
    },
    jsx: () => {
        return bundler().bundle()
            .pipe(source('app.js'))
            .on('error', log.error)
            .pipe(gulp.dest(`${buildPath}/js`));
    },
    fonts: () => {
        return gulp.src(`${srcPath}/fonts-list.txt`)
            .pipe(googleWebFonts({
                fontsDir: "fonts",
                cssDir: "css",
                cssFilename: "fonts.css"
            }))
            .pipe(gulp.dest(`${buildPath}`));
    },
    watch: () => {
        gulp.watch(`${srcPath}/scss/*.scss`, gulp.series(tasks.sass));
        gulp.watch(`${srcPath}/pug/*.pug`, gulp.series(tasks.pug));

        let b = bundler().plugin(watchify);
        let rebundle = () => {
            let startDate = new Date();

            return b
                .bundle(err => {
                    if (err) {
                        console.error(err.toString());
                    } else {
                        let now = new Date();
                        let secondsSpent = Math.round((now.getTime() - startDate.getTime()) / 1000);
                        let unit = secondsSpent > 1 ? 'seconds' : 'second';
                        console.log(`[${now.toLocaleTimeString()}] JSX compiled in ${secondsSpent} ${unit}`);
                    }
                })
                .pipe(source('app.js'))
                .pipe(gulp.dest(`${buildPath}/js`));
        };
        b.on('update', rebundle);

        return rebundle();
    }
};

gulp.task('build', gulp.series(tasks.clean, gulp.parallel(tasks.sass, tasks.pug, tasks.jsx, tasks.fonts)));
gulp.task('develop', gulp.series(tasks.clean, gulp.parallel(tasks.sass, tasks.pug, tasks.fonts), tasks.watch));
