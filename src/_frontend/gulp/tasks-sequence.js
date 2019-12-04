var gulp = require('gulp');
var runSequence = require('run-sequence');

gulp.task('default', ['task-sequence-devel-watch']);

gulp.task('prod', ['task-sequence-prod']);

gulp.task('devel', ['task-sequence-devel']);


gulp.task('task-sequence-devel-watch', function (callback) {
  runSequence(
    'task-sequence-devel',
    'watch',
    callback);
});

gulp.task('task-sequence-devel', function (callback) {
  process.env.NODE_ENV = 'devel';

  runSequence(
    'clean',
    [
      'sprite-common',
      'sprite-common-mobile',
      'svg-icons'
    ],
    'sass',
    'js',
    'vendors',
    callback);
});

gulp.task('task-sequence-prod', function (callback) {
  process.env.NODE_ENV = 'production';

  runSequence(
    'clean',
    [
      'sprite-common',
      'sprite-common-mobile',
      'svg-icons'
    ],
    'sass',
    'js',
    'vendors',
    callback);
});

