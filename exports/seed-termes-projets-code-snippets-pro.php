<?php
/**
 * Code Snippets Pro — crée les termes des formulaires Projet municipal.
 * Portée : « Exécuter partout » (ou « Exécuter partout dans l'administration »).
 * Attend l'enregistrement des taxonomies (init, priorité 99), puis s'exécute une seule fois.
 */
add_action( 'init', function () {
	if ( get_option( 'ck_seed_termes_projets_done' ) ) { return; }
	$termes = array(
		'ck_zone'  => array( 'Ensemble de la commune' ),
		'ck_theme' => array(
			'Administration et services publics', 'Conseil municipal', 'Finances et transparence',
			'Projets et infrastructures', 'Travaux et voirie', 'Eau et assainissement',
			'Environnement et cadre de vie', 'Éducation et formation', 'Santé et action sociale',
			'Jeunesse, sport et culture', 'Pêche, agriculture et élevage', 'Tourisme et économie locale',
			'Sécurité et prévention', 'Autre thématique',
		),
	);
	$crees = 0; $absentes = array();
	foreach ( $termes as $taxonomie => $noms ) {
		if ( ! taxonomy_exists( $taxonomie ) ) { $absentes[] = $taxonomie; continue; }
		foreach ( $noms as $nom ) {
			if ( ! term_exists( $nom, $taxonomie ) && ! is_wp_error( wp_insert_term( $nom, $taxonomie ) ) ) { $crees++; }
		}
	}
	if ( ! $absentes ) { update_option( 'ck_seed_termes_projets_done', 1 ); }
	update_option( 'ck_seed_termes_projets_resume', $crees . ' terme(s) créé(s)' . ( $absentes ? ' — taxonomie introuvable : ' . implode( ', ', $absentes ) : '' ) );
}, 99 );

add_action( 'admin_notices', function () {
	$resume = get_option( 'ck_seed_termes_projets_resume' );
	if ( $resume && current_user_can( 'manage_options' ) ) {
		echo '<div class="notice notice-info is-dismissible"><p><strong>Termes projets :</strong> ' . esc_html( $resume ) . '</p></div>';
	}
} );
