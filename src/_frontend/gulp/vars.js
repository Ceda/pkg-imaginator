var gulp = require('gulp');
var notify = require("gulp-notify"); // needed for swallowSassError
var browserSync = require('browser-sync').create();

// use default setting or custom from got ignored '_env.json'
var envConfig = require('../_env-default.json');
try {
  envConfig = require('../_env.json');
} catch (e) {

}

var includePaths = [
  //
];

var modernizr = {
  "crawl": false,
  "customTests": [],
  "tests": [
    "picture",
    "svgasimg"
  ],
  "options": [
    "hasEvent",
    "setClasses"
  ],
  "uglify": false
};

function swallowSassError(error) {
  console.log('--------ERROR-SASS--------');
  notify({title: error.relativePath}).write('[' + error.line + ':' + error.column + '] ' + error.messageOriginal);
  // console.log(error.messageFormatted);
  console.log('--------------------------');

  this.emit('end');
}

function swallowJsError(error) {
  console.log('--------ERROR-JS--------');
  notify({title: error.fileName}).write('JavaScript: ' + error.name + ' [' + error.loc.line + ':' + error.loc.column + ']');
  console.log('--------------------------');

  this.emit('end');
}

module.exports = {
  SOURCE_PATH: './',
  BUILD_PATH: '../assets/dist',
  SVGICONS_TARGETPATH: '../../../_frontend/sass/_generated/svg-icons.scss',
  modernizrOptions: modernizr,
  includePaths: includePaths,
  browserSync: browserSync,
  envConfig: envConfig,
  swallowSassError: swallowSassError,
  swallowJsError: swallowJsError
};
