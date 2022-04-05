const { src, dest, parallel } = require('gulp');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const assets = require('./assets.json');

function buildCurrentView(cb) {
    src(assets.current)
        .pipe(concat('current-view-all.min.js'))
        .pipe(uglify())
        .pipe(dest('./assets/'));

    cb();
}

function buildRoomView(cb) {
    src(assets.room)
        .pipe(concat('room-view-all.min.js'))
        .pipe(uglify())
        .pipe(dest('./assets/'))

    cb();
}

exports.default = parallel(buildCurrentView, buildRoomView)
