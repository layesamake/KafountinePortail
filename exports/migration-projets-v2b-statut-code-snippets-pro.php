<?php
/**
 * Code Snippets Pro — complément de la reprise des projets : le statut.
 * Portée : « Exécuter partout dans l'administration ». S'exécute une seule fois.
 * (La première reprise l'avait ignoré : ACF renvoie la valeur par défaut « À faire »
 * quand rien n'est enregistré, ce qui faisait croire que le champ était déjà rempli.)
 */
add_action( 'admin_init', function () {
	if ( get_option( 'ck_migration_projets_v2b_done' ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'acf_get_field_groups' ) ) {
		return;
	}
	$norm = function ( $s ) { return remove_accents( mb_strtolower( trim( (string) $s ) ) ); };
	$cle_ancien = null;
	foreach ( acf_get_field_groups( array( 'post_type' => 'ck_projet' ) ) as $groupe ) {
		if ( 'group_ck_projet_v2' === $groupe['key'] ) { continue; }
		foreach ( (array) acf_get_fields( $groupe ) as $champ ) {
			if ( 'statut' === $norm( $champ['label'] ) ) { $cle_ancien = $champ['key']; }
		}
	}
	$statuts = array(
		'prevu' => 'À faire', 'en preparation' => 'À faire', 'a faire' => 'À faire',
		'en cours' => 'En cours', 'suspendu' => 'En cours',
		'acheve' => 'Terminé', 'termine' => 'Terminé',
	);
	$n = 0;
	if ( $cle_ancien ) {
		foreach ( get_posts( array( 'post_type' => 'ck_projet', 'post_status' => 'any', 'numberposts' => -1 ) ) as $p ) {
			if ( metadata_exists( 'post', $p->ID, 'statut_projet' ) ) { continue; }
			$brut = $norm( (string) get_field( $cle_ancien, $p->ID, false ) );
			if ( isset( $statuts[ $brut ] ) ) { update_field( 'statut_projet', $statuts[ $brut ], $p->ID ); $n++; }
		}
	}
	update_option( 'ck_migration_projets_v2b_done', 1 );
	update_option( 'ck_migration_projets_v2b_resume', $n . ' statut(s) repris.' );
} );

add_action( 'admin_notices', function () {
	$resume = get_option( 'ck_migration_projets_v2b_resume' );
	if ( $resume && current_user_can( 'manage_options' ) ) {
		echo '<div class="notice notice-success is-dismissible"><p><strong>Migration projets v2 (statut) :</strong> ' . esc_html( $resume ) . '</p></div>';
	}
} );
