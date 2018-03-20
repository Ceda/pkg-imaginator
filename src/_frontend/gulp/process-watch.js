var vars = require('./vars');
var gulp = require('gulp');
var notify = require("gulp-notify");

gulp.task('watch', function () {
	let config = vars.envConfig;

	gulp.watch(vars.SOURCE_PATH + '/sass/**/*.scss', ['sass']);
	gulp.watch(vars.SOURCE_PATH + '/js/_concat.json', ['js']);
	gulp.watch(vars.SOURCE_PATH + '/js/**/*', ['js-nolibs']);

	gulp.watch(vars.SOURCE_PATH + '/admin/sass/**/*.scss', ['sass-admin']);
	gulp.watch(vars.SOURCE_PATH + '/admin/js/_concat.json', ['js-admin']);
	gulp.watch(vars.SOURCE_PATH + '/admin/js/**/*', ['js-nolibs-admin']);

	gulp.watch(vars.SOURCE_PATH + '/sprites/common/*.png', ['sprite-common']);
	gulp.watch(vars.SOURCE_PATH + '/sprites/common-mobile/*.png', ['sprite-common-mobile']);
	gulp.watch(vars.SOURCE_PATH + '/img/**/*.png', ['images-minify-png']);
	gulp.watch([vars.SOURCE_PATH + '/img/**/*.jpg', vars.SOURCE_PATH + '/img/**/*.gif', vars.SOURCE_PATH + '/img/**/*.svg'], ['images-minify-other']);

	gulp.watch(vars.SOURCE_PATH + '/svg_icons/*.svg', ['svg-icons']);
	gulp.watch(vars.SOURCE_PATH + '/svg_images/*.svg', ['svg-images']);

	gulp.watch([vars.SOURCE_PATH + '/templates/**/*.php'], ['templates']).on('change', function (file) {
		var filename = file.path;
		notify({title: 'Templates - reloading'}).write(
			'File ' + file.type + ': \n' + (filename.length > 40 ? '...' : '') + filename.substr(filename.length - 40)
		);
		vars.browserSync.reload();
	});

	vars.browserSync.init({
		proxy: config.PROXY || 'localhost',
		open: (config.PROXY_OPEN === 'true') || false,
		ghostMode: {
			clicks: config.browsersync.clicks,
			forms: config.browsersync.forms,
			scroll: config.browsersync.scroll
		}
	});
});
