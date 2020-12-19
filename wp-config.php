<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en « wp-config.php » et remplir les
 * valeurs.
 *
 * Ce fichier contient les réglages de configuration suivants :
 *
 * Réglages MySQL
 * Préfixe de table
 * Clés secrètes
 * Langue utilisée
 * ABSPATH
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'asaintlo' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'wordpress' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', 'wordpress' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/**
 * Type de collation de la base de données.
 * N’y touchez que si vous savez ce que vous faites.
 */
define( 'DB_COLLATE', '' );

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'yJ;^xS}FBpJdgoPvPRC{9/a<9Y[IXph|aQ4~ID}gQtK:l>JZ3cr1 OK3>q{WVqS*' );
define( 'SECURE_AUTH_KEY',  'Wj~<1G~>u]!(v/?I+39TE3>zN ehVj5(/T2 }nu%;ja1R1,PPkYy8w7oAuX9MQhv' );
define( 'LOGGED_IN_KEY',    '@=,M.#*j]MqX4@!j$R?MAKmp= %|Ni1E${>o_Kn|Gw?koPiS[1KNsijf4{!D.R*$' );
define( 'NONCE_KEY',        '= Iz|pA&I3yr2e<#ZA}eJ{to=*}WkvqnS{qWWT3b86[+!f@sf.UYzSyO5X-V5lP~' );
define( 'AUTH_SALT',        '#(m!D~9u?s2T6)>)BFZniirsCAfNww:2paGq/~&lFh76h7LkM,IGMiz9o4`Y7Q0{' );
define( 'SECURE_AUTH_SALT', 'BX&u:viUo|5)7-*0=aRL88gC5 |~3N9<zphGOMx_Pa=-<w,?ljs~P;Gp2Sm>R]R@' );
define( 'LOGGED_IN_SALT',   'EBp~XjS]blYU,xTG}3^HI)V>SWW#I2ssP,aE8/a%.tH--Po6>lk6FAG.5*{o_B*|' );
define( 'NONCE_SALT',       '=v(RpaKu2CrLs*oL$Jw2X9cF>Kw,^ X;6E5t4w.Uny^3b8T<I.F.)&D+&*q`I4Ht' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( ! defined( 'ABSPATH' ) )
  define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once( ABSPATH . 'wp-settings.php' );
