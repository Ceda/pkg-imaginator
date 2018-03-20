var vars = require('./vars');
var gulp = require('gulp');
var copy = require('gulp-copy');
var plumber = require('gulp-plumber');
var rename = require('gulp-rename');
var gulpIf = require('gulp-if');
var preprocess = require("gulp-preprocess");

var sass = require('gulp-sass');
var cleanCSS = require('gulp-clean-css');
var autoprefixer = require('gulp-autoprefixer');
var sourcemaps = require('gulp-sourcemaps');

function processSass (admin = false) {

	var semanticCoreFiles = !admin ? '/sass/**/build_**.**' : '/admin/sass/**/build_**.**',
		semanticSectionsFiles = !admin ? '/sass/sections/**.**' : '/admin/sass/sections/**.**',
		buildDestFolder = !admin ? '/css' : '/../admin/dist/css';

	var sassFiles = [
		vars.SOURCE_PATH + semanticCoreFiles,
		vars.SOURCE_PATH + semanticSectionsFiles
	];

	var isProduction = process.env.NODE_ENV === 'production';

	return gulp.src(sassFiles)
		.pipe(plumber())
		.pipe(gulpIf(!isProduction, sourcemaps.init()))
		.pipe(sass({includePaths: vars.includePaths}))
		.on('error', vars.swallowSassError)
		.pipe(autoprefixer({
			browsers: ['last 5 versions', 'ie >= 11'],
			cascade: false
		}))
		.pipe(preprocess({context: {TIMESTAMP: Date.now()}}))
		.pipe(gulpIf(isProduction, cleanCSS())) //{compatibility: 'ie8'}
		.pipe(rename(function (path) {
			path.dirname = '';
			path.basename = path.basename.replace('build_', '');
		}))
		.pipe(gulpIf(!isProduction, sourcemaps.write()))
		.pipe(gulp.dest(vars.BUILD_PATH + buildDestFolder))
		.pipe(vars.browserSync.stream());
}

gulp.task('sass', function () {
	processSass(false);
});

gulp.task('sass-admin', function () {
	processSass(true);
});