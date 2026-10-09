<?php
/** Import de la structure validée des commissions ; aucun rendu public ajouté. */
defined( 'ABSPATH' ) || exit;

final class CK_Commissions_V2 {
	private static function normalize( $value ) {
		return strtolower( preg_replace( '/\s+/u', ' ', trim( remove_accents( $value ) ) ) );
	}

	public static function run( $data, &$log ) {
		if ( ! post_type_exists( 'ck_commission' ) || ! post_type_exists( 'ck_elu' ) ) {
			throw new RuntimeException( 'Importez les types de contenus Commissions et Élus avant les données.' );
		}
		if ( ! function_exists( 'acf_get_field_type' ) || ! acf_get_field_type( 'repeater' ) || ! function_exists( 'update_field' ) ) {
			throw new RuntimeException( 'ACF Pro et son champ Répéteur sont nécessaires pour importer la composition.' );
		}
		foreach ( array( 'field_ck_commission_composition', 'field_ck_commission_numero_commission', 'field_ck_commission_source_document', 'field_ck_commission_ordre_affichage' ) as $field ) {
			if ( ! acf_get_field( $field ) ) {
				throw new RuntimeException( 'Importez d’abord 03-groupes-champs.json. Champ absent : ' . $field );
			}
		}
		// Validation complète avant toute écriture. Aucun identifiant local ne vaut ID WordPress.
		$numbers = array();
		foreach ( (array) ( $data['records'] ?? array() ) as $row ) {
			$number = (int) ( $row['numero_commission'] ?? 0 );
			if ( $number < 1 || isset( $numbers[ $number ] ) || empty( $row['title'] ) || ! isset( $row['composition'] ) || ! is_array( $row['composition'] ) ) {
				throw new RuntimeException( 'Commission invalide ou numéro en double.' );
			}
			$numbers[ $number ] = true;
			foreach ( $row['composition'] as $member ) {
				if ( ! in_array( $member['role'] ?? '', array( 'presidence', 'vice_presidence', 'rapporteur', 'membre' ), true ) || ! in_array( $member['statut_information'] ?? '', array( 'renseigne', 'a_renseigner', 'identite_a_confirmer' ), true ) ) {
					throw new RuntimeException( 'Rôle ou état de l’information invalide dans la commission ' . $number );
				}
			}
		}
		$index = array();
		foreach ( get_posts( array( 'post_type' => 'ck_elu', 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'posts_per_page' => -1 ) ) as $person ) {
			$index[ self::normalize( $person->post_title ) ][] = (int) $person->ID;
		}
		$count = 0;
		foreach ( (array) ( $data['records'] ?? array() ) as $row ) {
			$number = (int) $row['numero_commission'];
			$title = sanitize_text_field( $row['title'] );
			$key = 'commission:' . $number;
			$query = array( 'post_type' => 'ck_commission', 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'posts_per_page' => 1, 'fields' => 'ids' );
			$existing = get_posts( array_merge( $query, array( 'meta_key' => '_ck_import_key', 'meta_value' => $key ) ) );
			if ( ! $existing ) {
				$existing = get_posts( array_merge( $query, array( 'title' => $title ) ) );
			}
			if ( $existing ) {
				// Ne jamais effacer une composition déjà corrigée par un éditeur.
				$log[] = 'Commission ' . $number . ' : fiche existante conservée (ID ' . (int) $existing[0] . '). Vérifiez sa composition dans WordPress.';
				continue;
			}
			$composition = array();
			foreach ( $row['composition'] as $member ) {
				$name = sanitize_text_field( $member['nom_source'] ?? '' );
				$ids = $name ? ( $index[ self::normalize( $name ) ] ?? array() ) : array();
				$person_id = count( $ids ) === 1 ? $ids[0] : 0;
				$status = $name ? ( $person_id ? 'renseigne' : 'identite_a_confirmer' ) : 'a_renseigner';
				if ( $name && ! $person_id ) {
					$log[] = 'Commission ' . $number . ' : identité à vérifier — ' . $name . '. Aucun élu créé automatiquement.';
				}
				$values = array( 'elu' => $person_id ?: '', 'nom_source' => $name, 'role' => $member['role'], 'intitule_role' => sanitize_text_field( $member['intitule_role'] ?? '' ), 'statut_information' => $status, 'precision' => sanitize_text_field( $member['precision'] ?? '' ), 'ordre_affichage' => max( 1, (int) ( $member['ordre_affichage'] ?? 1 ) ) );
				$acf_row = array();
				foreach ( $values as $field => $value ) {
					$acf_row[ 'field_ck_commission_composition_' . $field ] = $value;
				}
				$composition[] = $acf_row;
			}
			$id = wp_insert_post( wp_slash( array( 'post_type' => 'ck_commission', 'post_title' => $title, 'post_status' => 'draft', 'menu_order' => $number ) ), true );
			if ( is_wp_error( $id ) ) {
				throw new RuntimeException( $id->get_error_message() );
			}
			update_field( 'field_ck_commission_composition', $composition, $id );
			update_field( 'field_ck_commission_numero_commission', $number, $id );
			update_field( 'field_ck_commission_ordre_affichage', $number, $id );
			update_field( 'field_ck_commission_source_document', sanitize_text_field( $data['source'] ?? '' ), $id );
			// Conserver la provenance complète sans l’injecter dans le contenu public.
			update_post_meta( $id, '_ck_commission_source', wp_slash( $row ) );
			update_post_meta( $id, '_ck_import_key', $key );
			update_post_meta( $id, '_ck_commission_schema', 2 );
			$log[] = 'Commission ' . $number . ' : créée en brouillon (ID ' . $id . ').';
			$count++;
		}
		return $count;
	}
}
