<?php
/**
 * Code Snippets Pro — reprise des données des projets vers le modèle v2.
 * Type d'extrait : PHP, portée « Exécuter partout dans l'administration ».
 * S'exécute une seule fois (drapeau ck_migration_projets_v2_done), copie sans rien supprimer.
 * Les anciens champs sont retrouvés par leur libellé : aucun nom technique à connaître.
 */
add_action( 'admin_init', function () {
	if ( get_option( 'ck_migration_projets_v2_done' ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'acf_get_field_groups' ) ) {
		return;
	}
	$norm = function ( $s ) { return remove_accents( mb_strtolower( trim( (string) $s ) ) ); };

	// 1. Anciens champs, retrouvés par libellé dans les groupes autres que le modèle v2.
	$anciens = array();
	foreach ( acf_get_field_groups( array( 'post_type' => 'ck_projet' ) ) as $groupe ) {
		if ( 'group_ck_projet_v2' === $groupe['key'] ) { continue; }
		foreach ( (array) acf_get_fields( $groupe ) as $champ ) {
			$anciens[ $norm( $champ['label'] ) ] = $champ['key'];
		}
	}
	$lire = function ( $libelles, $id ) use ( $anciens, $norm ) {
		foreach ( (array) $libelles as $libelle ) {
			$cle = $anciens[ $norm( $libelle ) ] ?? null;
			if ( $cle ) {
				$v = get_field( $cle, $id, false );
				if ( is_string( $v ) && '' !== trim( $v ) ) { return trim( $v ); }
			}
		}
		return '';
	};
	$statuts = array(
		'prevu' => 'À faire', 'en preparation' => 'À faire', 'a faire' => 'À faire',
		'en cours' => 'En cours', 'suspendu' => 'En cours',
		'acheve' => 'Terminé', 'termine' => 'Terminé',
	);

	// 2. Villages : le texte de localisation est comparé aux termes existants de ck_zone.
	$zones = get_terms( array( 'taxonomy' => 'ck_zone', 'hide_empty' => false ) );
	$zones = is_wp_error( $zones ) ? array() : $zones;

	$traites = 0; $details = array();
	$projets = get_posts( array( 'post_type' => 'ck_projet', 'post_status' => 'any', 'numberposts' => -1 ) );
	foreach ( $projets as $p ) {
		$fait = array();
		$id   = $p->ID;

		if ( ! get_field( 'statut_projet', $id, false ) ) {
			$brut = $norm( $lire( 'Statut', $id ) );
			if ( isset( $statuts[ $brut ] ) ) { update_field( 'statut_projet', $statuts[ $brut ], $id ); $fait[] = 'statut'; }
		}
		if ( ! get_field( 'etapes_avancement', $id, false ) ) {
			$v = $lire( array( 'Étapes et résultats', 'Étapes', 'Résultats' ), $id );
			if ( $v ) { update_field( 'etapes_avancement', $v, $id ); $fait[] = 'étapes'; }
		}
		if ( ! get_field( 'financement', $id, false ) ) {
			$v = $lire( 'Financement', $id );
			if ( $v ) { update_field( 'financement', $v, $id ); $fait[] = 'financement'; }
		}
		if ( '' === trim( $p->post_content ) ) {
			$v = $lire( 'Objectifs', $id );
			if ( $v ) { wp_update_post( array( 'ID' => $id, 'post_content' => $v ) ); $fait[] = 'contenu'; }
		}
		if ( ! wp_get_object_terms( $id, 'ck_zone', array( 'fields' => 'ids' ) ) ) {
			$loc = $norm( $lire( 'Localisation', $id ) );
			$ids = array();
			foreach ( $zones as $z ) {
				if ( '' !== $loc && false !== strpos( $loc, $norm( $z->name ) ) ) { $ids[] = $z->term_id; }
			}
			if ( $ids ) { wp_set_object_terms( $id, $ids, 'ck_zone' ); $fait[] = 'villages'; }
		}
		if ( $fait ) { $traites++; $details[] = $p->post_title . ' (' . implode( ', ', $fait ) . ')'; }
	}
	update_option( 'ck_migration_projets_v2_done', 1 );
	update_option( 'ck_migration_projets_v2_resume', $traites . ' projet(s) repris : ' . implode( ' ; ', $details ) );
} );

add_action( 'admin_notices', function () {
	$resume = get_option( 'ck_migration_projets_v2_resume' );
	if ( $resume && current_user_can( 'manage_options' ) ) {
		echo '<div class="notice notice-success is-dismissible"><p><strong>Migration projets v2 :</strong> ' . esc_html( $resume ) . '</p></div>';
	}
} );
