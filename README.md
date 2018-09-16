#Dojo WordPress Site

##Build SCSS
The styles for the site are written as SCSS and need to be compiled into CSS.
[GULP](https://gulpjs.com/) (V4 maybe?) is used to do this.
 
 Once installed running GULP from the base of the repo will watch the SCSS files and compile them for certain plugins and the theme. This can be extended in `gulpfile.js` in the root of the project.

##Local Dev Mode
You can set the local dev mode by creating the following environment variable:

`SetEnv DOJO_DEV_MODE yes`

This will prevent the custom logs location for the live server.