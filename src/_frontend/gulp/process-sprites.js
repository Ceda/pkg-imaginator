var vars = require('./vars');
var gulp = require('gulp');
var buffer = require('vinyl-buffer');
var notify = require("gulp-notify");

var rename = require('gulp-rename');
var spritesmith = require('gulp.spritesmith');
var imagePngquant = require('imagemin-pngquant');
var imageMin = require('gulp-imagemin');
var imageResize = require('gulp-image-resize');

gulp.task('sprite-common', function () {
  var spriteData = gulp.src(vars.SOURCE_PATH + '/sprites/common/*.png')
    .pipe(spritesmith({
      algorithm: 'left-right',
      algorithmOpts: {sort: false},
      imgName: 'sprite-common@2.png',
      cssName: 'sprite-common@2.scss'
    }));

  spriteData.img
    .pipe(buffer())
    .pipe(imagePngquant({quality: '25-35', speed: 1})())
    .pipe(imageMin())
    .pipe(gulp.dest(vars.BUILD_PATH + '/sprites/'))
    .pipe(notify({title: 'sprite-common', message: 'DONE'}));

  return spriteData.css
    .pipe(gulp.dest(vars.SOURCE_PATH + '/sass/_generated/'));
});

gulp.task('sprite-common-mobile', function () {
  var spriteData = gulp.src(vars.SOURCE_PATH + '/sprites/common-mobile/*.png')
    .pipe(spritesmith({
      algorithm: 'left-right',
      algorithmOpts: {sort: false},
      imgName: 'sprite-common-mobile@2.png',
      cssName: 'sprite-common-mobile@2.scss'
    }));

  spriteData.img
    .pipe(buffer())
    .pipe(imagePngquant({quality: '25-35', speed: 1})())
    .pipe(imageMin())
    .pipe(gulp.dest(vars.BUILD_PATH + '/sprites/'))
    .pipe(notify({title: 'sprite-common-mobile', message: 'DONE'}));

  return spriteData.css
    .pipe(gulp.dest(vars.SOURCE_PATH + '/sass/_generated/'));
});
