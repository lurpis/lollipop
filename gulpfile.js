var gulp = require('gulp');
var uglify = require('gulp-uglify');
var clean = require('gulp-clean-css');

gulp.task('css', function() {
  return gulp.src(['./public/src/style/main.css'])
    .pipe(clean())
    .pipe(gulp.dest('./public/asset/style'));
});

gulp.task('js', function() {
  return gulp.src(['./public/src/script/main.js','./public/src/script/router.js'])
  .pipe(uglify())
  .pipe(gulp.dest('./public/asset/script'))
});
