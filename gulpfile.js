var gulp = require('gulp');
var $ = require('gulp-load-plugins')();
var merge = require('merge2');


gulp.task('ts:dev', function () {
  var tsProj = $.typescript.createProject('tsconfig.json');
  
  return gulp.src(['./assets/ts/*{,/*}.ts', './typings/tsd.d.ts'])
    .pipe($.tslint())
    .pipe($.tslint.report('verbose'))
    .pipe($.sourcemaps.init())
    .pipe($.typescript(tsProj))
    .pipe($.sourcemaps.write())
    .pipe(gulp.dest('./assets/js'));
});

gulp.task('ts:dist', function () {
  var tsProj = $.typescript.createProject('tsconfig.json');
  
  return gulp.src(['./assets/ts/**/*.ts', './typings/tsd.d.ts'])
    .pipe($.sourcemaps.init())
    .pipe($.typescript(tsProj))
    .pipe($.uglify())
    .pipe($.sourcemaps.write('./assetst/js'))
    .pipe(gulp.dest('./assets/js'));
});

gulp.task('sass:dev', function () {
  return gulp.src('./assets/sass/**/*.{scss,sass}')
    .pipe($.sourcemaps.init())
    .pipe($.sass({includePaths: ['./bower_components']}).on('error', $.sass.logError))
    .pipe($.sourcemaps.write())
    .pipe(gulp.dest('./assets/css'));
});

gulp.task('sass:dist', function () {
  return gulp.src('./assets/sass/**/*.{scss,sass}')
    .pipe($.sourcemaps.init())
    .pipe($.sass({includePaths: ['./bower_components']}).on('error', $.sass.logError))
    .pipe($.uglify())
    .pipe($.sourcemaps.write('./assets/css'))
    .pipe(gulp.dest('./assets/css'));
});

gulp.task('watch', ['ts:dev', 'sass:dev'], function () {
  gulp.watch('./assets/sass/**/*.{scss,sass}', ['sass:dev']);
  gulp.watch('./assets/ts/**/*.ts', ['ts:dev']);
});

gulp.task('build', ['ts:dist', 'sass:dist']);
