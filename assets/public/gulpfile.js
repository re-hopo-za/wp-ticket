var gulp = require('gulp');
var rename = require('gulp-rename');
var jshint = require('gulp-jshint');
var concat = require('gulp-concat');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var minifyCss = require('gulp-clean-css');

var PUBLIC_DEST = './';
var JS_SOURCE = 'js';
var CSS_SOURCE = 'css';
var JS_OUTPUT_FILE = 'script.min.js';
var JS_OUTPUT_ALL = 'final.min.js';
var CSS_OUTPUT_FILE = 'style.css';


gulp.task('javascript', function () {
    return gulp.src([
        PUBLIC_DEST+JS_SOURCE + '/ticket.js',
    ])
    // .pipe(jshint())
    // .pipe(jshint.reporter('default'))
    .pipe(concat(JS_OUTPUT_FILE))
    .pipe(gulp.dest(PUBLIC_DEST));
});

gulp.task('javascript_compactor',gulp.series('javascript',  function () {
    return gulp.src([
        PUBLIC_DEST+JS_SOURCE + '/ckeditor.js',
        PUBLIC_DEST+JS_SOURCE + '/dropzone.min.js',
        PUBLIC_DEST+JS_SOURCE + '/prism.js',
        PUBLIC_DEST+JS_SOURCE + '/prism-autoloader.js',
        PUBLIC_DEST+JS_SOURCE + '/izi-toast.min.js',
        PUBLIC_DEST + JS_OUTPUT_FILE,
    ])
    .pipe(concat(JS_OUTPUT_ALL))
    .pipe(gulp.dest(PUBLIC_DEST));
}));



gulp.task('css_creator', function () {
    return gulp.src([
        PUBLIC_DEST+CSS_SOURCE + '/*.scss' ,
        PUBLIC_DEST+CSS_SOURCE + '/*.css'
    ])
    .pipe(sass())
    .pipe(autoprefixer('last 2 versions'))
    .pipe(concat(CSS_OUTPUT_FILE))
    .pipe(gulp.dest(PUBLIC_DEST))
    .pipe(rename({suffix: '.min'}))
    .pipe(minifyCss())
    .pipe(gulp.dest(PUBLIC_DEST));
});


gulp.task('default', gulp.series(
        'css_creator',
        'javascript_compactor',
        function () {
            gulp.watch([PUBLIC_DEST+JS_SOURCE + '/*.js'], gulp.series(['javascript_compactor']));
            gulp.watch([PUBLIC_DEST+CSS_SOURCE + '/*.scss'], gulp.series('css_creator'));
        }));
