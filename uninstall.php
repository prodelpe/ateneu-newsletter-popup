<?php
/**
 * Executat per WordPress quan l'usuari desinstal·la el plugin.
 * Esborra les opcions de la base de dades.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'anp_settings' );
