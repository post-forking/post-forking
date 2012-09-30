#!/bin/sh


type -P cleancss  &>/dev/null  && continue  || { echo "Install clean-css (https://github.com/GoalSmashers/clean-css) in order to minify CSS "; exit 1; }
type -P uglifyjs  &>/dev/null  && continue  || { echo "Install UglifyJS (https://github.com/mishoo/UglifyJS) in order to minify JS"; exit 1; }

echo "Dependencies met.  Minifying CSS \n"
cleancss css/admin.css > css/admin.min.css
echo "CSS Minified.  Now minifiying JS \n"
uglifyjs js/admin.js > js/admin.min.js
echo "All Done.  Have a great day \n"
