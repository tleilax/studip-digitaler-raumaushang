var gulp = require('gulp'),
    merge = require('merge-stream'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    minifyCss = require('gulp-minify-css'),
    convertEncoding = require('gulp-convert-encoding'),
    rename = require('gulp-rename');

gulp.task('js', function () {
    var assets = require('./assets.json');

    return merge(
        gulp.src(assets.current)
            .pipe(concat('current-view-all.min.js'))
            .pipe(uglify())
            .pipe(convertEncoding({to: 'iso-8859-1'}))
            .pipe(gulp.dest('./assets/')),
        gulp.src(assets.room)
            .pipe(concat('room-view-all.min.js'))
            .pipe(uglify())
            .pipe(convertEncoding({to: 'iso-8859-1'}))
            .pipe(gulp.dest('./assets/'))
    );
});

gulp.task('assets', ['js'], function () {
    return gulp;
});
