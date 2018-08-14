const gulp = require('gulp');
const scss = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');


const dirTheme = 'wp-content/themes/dojo-astrid-child';
const dirSrcScss = dirTheme + '/scss';
const fileScss = dirTheme + '/scss/style.scss';

gulp.task('css', function () {
  return gulp.src(fileScss)
    .pipe(sourcemaps.init())
    .pipe(scss())
    .pipe(gulp.dest(dirTheme));
});

gulp.task('assets', ['css', 'js']);


gulp.task('local-watch', function () {
  gulp.watch([dirSrcScss + '/**/*'], ['css']);
});

gulp.task('default', ['local-watch']);
