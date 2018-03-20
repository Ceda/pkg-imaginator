var vars = require('./vars');
var gulp = require('gulp');
var del = require('del');

function processClean(admin = false) {
	let toDelete = [
		vars.BUILD_PATH + "/**/*",
		'./sass/_generated/**/*'
	];

	if (admin) {
		toDelete = [
			vars.BUILD_PATH + "/../admin/dist"
		];
	}

	return del(toDelete, {
		force: true
	});
}

gulp.task('clean', function () {
	processClean(false);
});

gulp.task('clean-admin', function () {
	processClean(true);
});