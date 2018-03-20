var vars = require('./vars');
var gulp = require('gulp');
var gulpIf = require('gulp-if');
var plumber = require('gulp-plumber');
var notify = require("gulp-notify");
var preprocess = require("gulp-preprocess");

// js
var cache = require('gulp-cached');
var uglify = require('gulp-uglify');
var babel = require('gulp-babel');
var concat = require('gulp-concat');
var jshint = require('gulp-jshint');
var jshintStylish = require('jshint-stylish');
var sourcemaps = require('gulp-sourcemaps');

function processJavascript(full, admin = false) {

	var pathToConcat = !admin ? '../js/_concat.js' : '../admin/js/_concat.js';
	var bildDestPath = !admin ? '/js' : '/../admin/dist/js';

	var files = require(pathToConcat);

	var isProduction = process.env.NODE_ENV === 'production';

	for(packageName in files) {
		var package = files[packageName];

		if (packageName.indexOf('libs') === -1) {
			// dont lint & minify libs

			gulp.src(package)
				.pipe(plumber())
				.pipe(jshint({esversion: 6}))
				// Use gulp-notify as jshint reporter
				.pipe(notify({
					title: 'JS LINT', message: function (file) {
						if (file.jshint.success) {
							// Don't show something if success
							return false;
						}

						var errors = file.jshint.results.map(function (data) {
							if (data.error) {
								return "[" + data.error.line + ':' + data.error.character + '] ' + data.error.reason;
							}
						}).join("\n");
						var errorMessage = file.relative + " (" + file.jshint.results.length + " errors)\n" + errors;

						return errorMessage;
					}
				}))
				.pipe(preprocess())
				.pipe(gulpIf(!isProduction, sourcemaps.init()))
				.pipe(babel({presets: ['es2015']}))
				.on('error', vars.swallowJsError)
				.pipe(concat(packageName + '.js', {newLine: ';;'}))
				.pipe(gulpIf(isProduction, uglify()))
				.pipe(gulpIf(!isProduction, sourcemaps.write()))
				.pipe(gulp.dest(vars.BUILD_PATH + bildDestPath))
				.pipe(vars.browserSync.stream())
			;

		} else if (full === true) {
			// just concat libs

			gulp.src(package)
				.on('error', vars.swallowJsError)
				.pipe(concat(packageName + '.js', {newLine: ';;'}))
				.pipe(gulpIf(isProduction, uglify()))
				.pipe(gulp.dest(vars.BUILD_PATH + bildDestPath))
				.pipe(vars.browserSync.stream())
			;

		}
	}
}

gulp.task('js-nolibs', function () {
	processJavascript(false, false);
});

gulp.task('js-nolibs-admin', function () {
	processJavascript(false, true);
});

gulp.task('js', function () {
	processJavascript(true, false);
});

gulp.task('js-admin', function () {
	processJavascript(true, true);
});
