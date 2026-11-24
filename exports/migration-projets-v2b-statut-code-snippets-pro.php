<?php
/**
 * Code Snippets Pro — complément de la reprise des projets : le statut.
 * Portée : « Exécuter partout dans l'administration ». S'exécute une seule fois.
 * (La première reprise l'avait ignoré : ACF renvoie la valeur par défaut « À faire »
 * quand rien n'est enregistré, ce qui faisait croire que le champ était déjà rempli.)
 */
add_action( 'admin_init', function () {
	if ( get_option( 'ck_migration_projets_v2c_done' ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'acf_get_field_groups' ) ) {
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
	// Tolérant : la valeur stockée peut être un libellé ou un identifiant (en_cours, en-cours…).
	$convertir = function ( $brut ) use ( $norm ) {
		$v = $norm( str_replace( array( '_', '-' ), ' ', (string) $brut ) );
		if ( false !== strpos( $v, 'cours' ) || false !== strpos( $v, 'suspen' ) ) { return 'En cours'; }
		if ( false !== strpos( $v, 'acheve' ) || false !== strpos( $v, 'termin' ) ) { return 'Terminé'; }
		if ( false !== strpos( $v, 'prevu' ) || false !== strpos( $v, 'prepar' ) || false !== strpos( $v, 'faire' ) ) { return 'À faire'; }
		return '';
	};
	$n = 0; $diag = array();
	foreach ( get_posts( array( 'post_type' => 'ck_projet', 'post_status' => 'any', 'numberposts' => -1 ) ) as $p ) {
		$brut   = $cle_ancien ? get_field( $cle_ancien, $p->ID, false ) : null;
		$existe = metadata_exists( 'post', $p->ID, 'statut_projet' );
		$cible  = $convertir( $brut );
		$diag[] = mb_substr( $p->post_title, 0, 25 ) . ' [ancien=' . var_export( $brut, true ) . ', déjà_enregistré=' . ( $existe ? 'oui' : 'non' ) . ']';
		if ( $cible && ( ! $existe || 'À faire' === get_post_meta( $p->ID, 'statut_projet', true ) ) ) {
			update_field( 'statut_projet', $cible, $p->ID ); $n++;
		}
	}
	if ( ! $cle_ancien ) { $diag[] = 'ancien champ « Statut » introuvable'; }
	update_option( 'ck_migration_projets_v2c_done', 1 );
	update_option( 'ck_migration_projets_v2c_resume', $n . ' statut(s) repris. ' . implode( ' ; ', $diag ) );
} );

add_action( 'admin_notices', function () {
	$resume = get_option( 'ck_migration_projets_v2c_resume' );
	if ( $resume && current_user_can( 'manage_options' ) ) {
		echo '<div class="notice notice-success is-dismissible"><p><strong>Migration projets v2 (statut) :</strong> ' . esc_html( $resume ) . '</p></div>';
	}
} );
