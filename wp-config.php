<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

define('WP_HOME', getenv('WP_URL'));
define('WP_SITEURL', getenv('WP_URL'));

@ini_set('log_errors','On'); // enable or disable php error logging (use 'On' or 'Off')
@ini_set('display_errors','Off'); // enable or disable public display of errors (use 'On' or 'Off')
@ini_set('error_log','/var/www/html/dojo_php.log'); // path to server-writable log file

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', getenv('DATABASE_NAME'));

/** MySQL database username */
define('DB_USER', getenv('DATABASE_USER'));

/** MySQL database password */
define('DB_PASSWORD', getenv('DATABASE_PASSWORD'));

/** MySQL hostname */
define('DB_HOST', getenv('DATABASE_HOST'));

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');


// ** EXTERNAL LOGIN SETTINGS ** //
/** EXLOG - The External Database Name */
define('EXTERNAL_LOGIN_OPTION_DB_NAME', getenv('EXTERNAL_LOGIN_OPTION_DB_NAME'));

/** EXLOG - The External Database Host */
define('EXTERNAL_LOGIN_OPTION_DB_HOST', getenv('EXTERNAL_LOGIN_OPTION_DB_HOST'));

/** EXLOG - The External Database Port */
define('EXTERNAL_LOGIN_OPTION_DB_PORT', getenv('EXTERNAL_LOGIN_OPTION_DB_PORT'));

/** EXLOG - The External Database Username */
define('EXTERNAL_LOGIN_OPTION_DB_USERNAME', getenv('EXTERNAL_LOGIN_OPTION_DB_USERNAME'));

/** EXLOG - The External Database Password */
define('EXTERNAL_LOGIN_OPTION_DB_PASSWORD', getenv('EXTERNAL_LOGIN_OPTION_DB_PASSWORD'));

/** EXLOG - Hash Type */
define('EXTERNAL_LOGIN_OPTION_HASH_ALGORITHM', getenv('EXTERNAL_LOGIN_OPTION_HASH_ALGORITHM'));

/** EXLOG - Salting Method */
define('EXTERNAL_LOGIN_OPTION_DB_SALTING_METHOD', getenv('EXTERNAL_LOGIN_OPTION_DB_SALTING_METHOD'));

/** EXLOG - Salt Location */
define('EXTERNAL_LOGIN_OPTION_DB_SALT_LOCATION', getenv('EXTERNAL_LOGIN_OPTION_DB_SALT_LOCATION'));

/** EXLOG - Password Salt */
define('EXTERNAL_LOGIN_OPTION_DB_SALT', getenv('EXTERNAL_LOGIN_OPTION_DB_SALT'));


/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '}~AFRzmX*=!P[4T/z~NMgWRz`lJJ!`7:CHRg1-?9`pw%n)8g!{5KGWuyivs)a5~C');
define('SECURE_AUTH_KEY',  '_x=@j[<@R-1BXWS3h8lgUJ+D%8.>lwM7MuVsb,Q|G*+qDN_URU/ W/.1R{4~l~l!');
define('LOGGED_IN_KEY',    'y7EGX9G*%P~QPZW65Ox6>bHHl#_Kh;i</l+_T),;h1c3sTQ=,bb=-z]MFD/+Bst,');
define('NONCE_KEY',        'M[]/p)PJXW1wfg&_w%R8IC0M,mtZ!cb,cn6UU&R/WiO/NCciG{+pdiul#,gx|>@2');
define('AUTH_SALT',        '[C-?f1(@zOQVlz*09FXmsZ,7|vCpB/UptH+G[>4h>*VdY2s%Qt=iTOjrw W0b7_2');
define('SECURE_AUTH_SALT', 'v7b[0Cc4#_]_|zO<5l|B3p(y:@|7 eU7q0p*={5YcS;R?n#>n~pL8s<]V^RM1brH');
define('LOGGED_IN_SALT',   '1`yBw/5_%S})S11}F))Bia0QMEW/|EU[R{C^@K|lLv6[[tt`:y`k33zBsg]OK(|s');
define('NONCE_SALT',       '/XT,]s9FQK9J{RUG.q?r|s8kV2@4%]PeJoR~*_zw<JurwoNlmP5,*f/zIx^l:qf9');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
