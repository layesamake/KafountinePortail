<?php
/**
 * Uninstall handler for CK Admin communal.
 * The plugin stores one version marker for role synchronization.
 * It does not create posts, terms, media or user accounts.
 */
if ( function_exists( 'delete_option' ) ) {
	delete_option( 'ck_admin_communal_roles_version' );
}