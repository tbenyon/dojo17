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

gulp.task('cssPluginCcs', function () {
  return gulp.src('wp-content/plugins/benyon-core-content-sections/styles/benyon_ccs_styles.scss')
    .pipe(sourcemaps.init())
    .pipe(scss())
    .pipe(gulp.dest('wp-content/plugins/benyon-core-content-sections'));
});

gulp.task('cssPluginAcc', function () {
  return gulp.src('wp-content/plugins/benyon-accordion/benyon-accordion.scss')
    .pipe(sourcemaps.init())
    .pipe(scss())
    .pipe(gulp.dest('wp-content/plugins/benyon-accordion'));
});

gulp.task('assets', ['css', 'js']);


gulp.task('local-watch', function () {
  gulp.watch(['wp-content/themes/**/*', 'wp-content/plugins/**/*'], ['css', 'cssPluginCcs', 'cssPluginAcc']);
});

gulp.task('default', ['local-watch']);
