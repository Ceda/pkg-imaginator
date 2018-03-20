var vars = require('./vars');
var {map, afterCopyCallback} = require('../js/_vendors.js');
var gulp = require('gulp');

gulp.task('vendors', function () {
	var streams = [];
	var finishedStreamsCount = 0;

	for (let nodeFolder in map) {
		if (map.hasOwnProperty(nodeFolder) === false) {
			continue;
		}

		let source = './node_modules/' + nodeFolder;
		let destination = vars.BUILD_PATH + '/vendor/' + map[nodeFolder];
		let stream = gulp.src(source).pipe(gulp.dest(destination));

		streams.push(stream);
		stream.on('end', function () {
			finishedStreamsCount++;
			if (finishedStreamsCount === streams.length) {
				afterCopyCallback();
			}
		});
	}
});
