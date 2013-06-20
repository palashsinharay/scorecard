<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'scorecard');

/** MySQL database username */
define('DB_USER', 'palash');

/** MySQL database password */
define('DB_PASSWORD', '123');

/** MySQL hostname */
define('DB_HOST', '192.168.1.16');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         '/}dVi&*H*$hHVtBu$8vB^`@V[<)z/{RJ=fN3`zgwAC/[.|}k[Y!k#7lgK[{,Y^%g');
define('SECURE_AUTH_KEY',  '1@e`jA9?PaTw+*F$RVADR}0sf[qEA=n`CZ-Fl:u=I(oo!v3f#ea;40vQyLStZI/g');
define('LOGGED_IN_KEY',    's8(8 +9?__dW9U`(T?a1fEs8Q3(e$5c7)F|mu3EqgjAA9XDVlbuhQp%s{6/!n cr');
define('NONCE_KEY',        'azQ2]j|.7B;.1rF$|>8~:_D4KyXAxj #Mz*i>.{LOX}0IyG`C[j5v#,v{5?e:~5H');
define('AUTH_SALT',        'f6~;[;K{$IlF4OaT%@$Y{C]@IqY?6b<<+#]0}3[DG$:(J:`[Eq&kX7B|ae<zO<e_');
define('SECURE_AUTH_SALT', 'F*-=WW6.>a)7u`0aQ#BH(4#kFRzvch6`N1h:O+E.gYs~0`A 1ODp1Nw2UvoDR^JX');
define('LOGGED_IN_SALT',   'J7]pf[yh Kq[QIXz{*~/|u5*o@=KzSJ{Rn:-Ss6815x36c`HT;a.u Q^t@cyEwS]');
define('NONCE_SALT',       '*{4.vjLeXo{+L^yjuSgMBp7%LB+gG}x|x0CUA@BNt;7rAg:ieAY~LEssEn&}SS!-');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
