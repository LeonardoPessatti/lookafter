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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'lookafter');

/** MySQL database username */
define('DB_USER', 'leozito');

/** MySQL database password */
define('DB_PASSWORD', 'root12345');

/** MySQL hostname */
define('DB_HOST', 'mysql472.umbler.com');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'c>ct^;Lj)|?6};FA$Pz6%E,@DxG;FtinLkzp,UBEw,F4w%6Hc~sshRsiu_,,y~C(');
define('SECURE_AUTH_KEY',  'tw6oOw|.EN2#Z:$vJ5`B(jnZ4(:2W?T1O%R^d*7rR*.kPy#Dx(_d#=XJ`JXOJwQH');
define('LOGGED_IN_KEY',    '9[akd1r;YU?8cP{x05#5|>n N u51V&x9 (Uz0{&nZ5,{>M=Iz8:jw_-x,F:GGD)');
define('NONCE_KEY',        '0L.AsBcn5P K+XZY~/#<17:at5,2mS~Bk:^2=; 7lz6?*&%8xsw<ggHaVx|]e_Bz');
define('AUTH_SALT',        '8$`Vuw2#N+* ]nP2@7X]$u6Y&Wm=.$aVLS{SY/r,ro*waI/rQ,eAaH:>gq$m.]#m');
define('SECURE_AUTH_SALT', 'T)|K-_3.qj)UcW@7i1LB`;jpY rTe0{*SNb||fasWjtPS8tLVn>#_Kpf5@Fz|m{?');
define('LOGGED_IN_SALT',   'bQ&$)s=@tM!xtwf=28O,g-Q[:0)Mf$n={.?!;zto4pe^~~Rv>ix}%`a9j->L[r6K');
define('NONCE_SALT',       'i4j_o7RimirZ<nTf]j`M[n3lBH6nKrsA?6)gtVJ- CPjCh@h4eH!u=!$TKjU7=`E');

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
