var vars = require('./vars');
var gulp = require('gulp');
var preprocess = require('gulp-preprocess');

gulp.task('templates', function () {
	gulp.src(['./devel/templates/**/*.php'])
		.pipe(preprocess())
		.pipe(gulp.dest(vars.BUILD_PATH + '/../../templates'));
});
