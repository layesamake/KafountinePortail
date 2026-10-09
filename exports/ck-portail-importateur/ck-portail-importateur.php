<?php
/**
 * Plugin Name: CK Portail institutionnel — Importateur
 * Description: Import idempotent des élus, commissions et agents depuis les JSON du portail institutionnel.
 * Version: 1.2.0
 * Author: Commune de Kafountine
 */

defined( 'ABSPATH' ) || exit;

final class CK_Portail_Importateur {

	const META_KEY = '_ck_import_key';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_shortcode( 'ck_organigramme', array( $this, 'organigramme_shortcode' ) );
	}

	/**
	 * Renders the public organisational chart from the imported services and agents.
	 * The grouping is the official hierarchy; names and job titles always come from
	 * the corresponding ck_agent records.
	 */
	public function organigramme_shortcode() {
		if ( ! post_type_exists( 'ck_service' ) || ! post_type_exists( 'ck_agent' ) ) {
			return current_user_can( 'manage_options' ) ? '<p>Importez d’abord les types de contenus et les données du personnel.</p>' : '';
		}

		$hierarchy = array(
			'Cabinet du maire' => array(
				'heading' => 'Cabinet du maire',
				'lead_role' => 'Chef de cabinet',
				'direct_members' => true,
				'children' => array(),
			),
			'Secrétariat et services rattachés' => array(
				'heading' => 'Secrétariat et services rattachés',
				'lead_role' => 'Secrétaire municipal',
				'children' => array( 'Courrier et archives', 'ASP', 'Bibliothécaires', 'Veilleurs de nuit', 'Radio communautaire' ),
			),
			'Division administration générale et des finances' => array(
				'heading' => 'Division administration générale et des finances',
				'lead_role' => 'Chef de division',
				'children' => array( 'Bureau finances, budget et comptabilité des matières', 'Agents recouvrements' ),
			),
			'Division des services techniques' => array(
				'heading' => 'Division des services techniques',
				'lead_role' => 'Chef de division',
				'children' => array( 'Bureau planification, études et contrôle', 'Bureau voirie, réseaux, entretien et maintenance', 'Bureau patrimoine, domaines, aménagement urbain et cadre de vie', 'Agents nettoiement', 'Techniciens de surface' ),
			),
			'Division état civil' => array(
				'heading' => 'Division état civil',
				'lead_role' => 'Cheffe de division',
				'children' => array( 'Bureau état civil', 'Agents état civil' ),
			),
		);

		$service = function( $title ) {
			$posts = get_posts( array(
				'post_type'      => 'ck_service',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'title'          => $title,
			) );
			return empty( $posts ) ? null : $posts[0];
		};
		$agents = function( $service_id, $role = '' ) {
			$query = array(
				'post_type'      => 'ck_agent',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_key'       => 'service',
				'meta_value'     => $service_id,
			);
			if ( $role ) {
				$query['meta_query'] = array(
					'relation' => 'AND',
					array( 'key' => 'service', 'value' => $service_id ),
					array( 'key' => 'fonction', 'value' => $role ),
				);
				unset( $query['meta_key'], $query['meta_value'] );
			}
			return get_posts( $query );
		};
		$render_people = function( $people ) {
			if ( ! $people ) {
				return '<p class="ck-empty">Titulaire non renseigné</p>';
			}
			return '<p class="ck-names">' . implode( '<br>', array_map( function( $person ) { return esc_html( get_the_title( $person ) ); }, $people ) ) . '</p>';
		};
		$render_card = function( $title ) use ( $service, $agents, $render_people ) {
			$item = $service( $title );
			if ( ! $item ) {
				return '';
			}
			$people = $agents( $item->ID );
			$roles = array_unique( array_filter( array_map( function( $person ) { return get_post_meta( $person->ID, 'fonction', true ); }, $people ) ) );
			return '<li class="ck-card"><h4>' . esc_html( get_the_title( $item ) ) . '</h4>' . ( $roles ? '<p class="ck-role">' . esc_html( implode( ' · ', $roles ) ) . '</p>' : '' ) . $render_people( $people ) . '</li>';
		};
		$render_person_card = function( $person ) {
			$role = get_post_meta( $person->ID, 'fonction', true );
			return '<li class="ck-card"><h4>' . esc_html( $role ?: 'Fonction à renseigner' ) . '</h4><p class="ck-names">' . esc_html( get_the_title( $person ) ) . '</p></li>';
		};
		$render_group = function( $key, $config ) use ( $service, $agents, $render_people, $render_card, $render_person_card ) {
			$item = $service( $key );
			if ( ! $item ) {
				return '';
			}
			$leaders = $agents( $item->ID, $config['lead_role'] );
			$children = '';
			if ( ! empty( $config['direct_members'] ) ) {
				$leader_ids = wp_list_pluck( $leaders, 'ID' );
				foreach ( $agents( $item->ID ) as $person ) {
					if ( ! in_array( $person->ID, $leader_ids, true ) ) {
						$children .= $render_person_card( $person );
					}
				}
			}
			foreach ( $config['children'] as $child ) {
				$children .= $render_card( $child );
			}
			return '<section class="ck-panel"><div class="ck-panel-head"><h3>' . esc_html( $config['heading'] ) . '</h3><p class="ck-role">' . esc_html( $config['lead_role'] ) . '</p>' . $render_people( $leaders ) . '</div>' . ( $children ? '<ul class="ck-list">' . $children . '</ul>' : '' ) . '</section>';
		};

		$cabinet = $render_group( 'Cabinet du maire', $hierarchy['Cabinet du maire'] );
		$secretariat = $render_group( 'Secrétariat et services rattachés', $hierarchy['Secrétariat et services rattachés'] );
		$divisions = '';
		foreach ( array( 'Division administration générale et des finances', 'Division des services techniques', 'Division état civil' ) as $division ) {
			$divisions .= $render_group( $division, $hierarchy[ $division ] );
		}
		if ( ! $cabinet && ! $secretariat && ! $divisions ) {
			return current_user_can( 'manage_options' ) ? '<p>Publiez les services et agents importés pour afficher l’organigramme.</p>' : '';
		}
		$maire_id = function_exists( 'get_field' ) ? (int) get_field( 'maire', 'option' ) : 0;
		$maire_name = $maire_id ? get_the_title( $maire_id ) : '';

		ob_start();
		?>
		<style>
		.ck-org{--ck-deep:#0B4C82;--ck-blue:#1568A6;--ck-ink:#1C2B3A;--ck-sun:#F0791A;--ck-sand:#F7EEDD;--ck-card:#F4F1EC;--ck-border:#D7D3CC;--ck-muted:#8A857D;color:var(--ck-ink);font:400 16px/1.65 Inter,Arial,sans-serif;background:var(--ck-sand)}.ck-org *{box-sizing:border-box}.ck-org h1,.ck-org h2,.ck-org h3,.ck-org h4,.ck-org p{margin:0}.ck-org .ck-wrap{max-width:1240px;margin:auto;padding:0 max(5%,calc((100% - 1240px)/2))}.ck-org .ck-hero{background:var(--ck-deep);color:#fff;padding:72px 0}.ck-org .ck-eyebrow{font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px}.ck-org h1,.ck-org h2,.ck-org h3{font-family:Poppins,Arial,sans-serif;font-weight:700}.ck-org h1{font-size:clamp(3rem,2.6151rem + 1.7105vw,3.8125rem);letter-spacing:-.02em;line-height:1.1}.ck-org .ck-intro{max-width:660px;margin-top:18px;font-size:18px}.ck-org .ck-body{padding-top:48px;padding-bottom:64px}.ck-org .ck-top{max-width:360px;margin:0 auto;text-align:center;padding:24px;background:var(--ck-deep);color:#fff;border-bottom:4px solid var(--ck-sun);box-shadow:0 2px 4px rgba(28,43,58,.08)}.ck-org .ck-top h2{font-size:24px}.ck-org .ck-mayor-name{font-size:14px;font-weight:600;margin-top:6px}.ck-org .ck-stem{width:2px;height:34px;background:var(--ck-blue);margin:auto}.ck-org .ck-branches{display:grid;grid-template-columns:1fr 1fr;gap:24px}.ck-org .ck-panel{border:1px solid var(--ck-border);background:#fff;overflow:hidden;box-shadow:0 2px 4px rgba(28,43,58,.08)}.ck-org .ck-panel-head{background:var(--ck-card);padding:24px;border-left:5px solid var(--ck-sun)}.ck-org h2{font-size:clamp(2.5rem,2.2336rem + 1.1842vw,3.0625rem);letter-spacing:.015em;line-height:1.15}.ck-org h3{font-size:clamp(1.4375rem,1.3783rem + .2632vw,1.5625rem);line-height:1.2}.ck-org .ck-role{font-size:12px;color:var(--ck-muted);margin-top:6px}.ck-org .ck-names{font-size:14px;margin-top:10px;font-weight:600;color:var(--ck-deep)}.ck-org .ck-empty{font-size:13px;color:var(--ck-muted);font-style:italic;margin-top:10px}.ck-org .ck-list{list-style:none;padding:20px;margin:0;display:grid;gap:12px}.ck-org .ck-card{border:1px solid var(--ck-border);border-left:3px solid var(--ck-blue);padding:16px;background:#fff}.ck-org h4{font-family:Inter,Arial,sans-serif;font-size:14px;line-height:1.45}.ck-org .ck-divisions{margin-top:48px}.ck-org .ck-section-title{margin-bottom:8px}.ck-org .ck-section-lead,.ck-org .ck-legend,.ck-org .ck-footer{color:var(--ck-muted);font-size:14px}.ck-org .ck-section-lead{margin-bottom:24px}.ck-org .ck-legend{font-size:12px;text-align:center;margin-top:20px}.ck-org .ck-footer{padding-top:24px}.ck-org .ck-division-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}.ck-org .ck-division-grid .ck-panel-head{min-height:164px}@media(max-width:850px){.ck-org .ck-division-grid{grid-template-columns:1fr}.ck-org .ck-division-grid .ck-panel-head{min-height:0}.ck-org .ck-division-grid .ck-list{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.ck-org .ck-wrap{padding:0 20px}.ck-org .ck-hero{padding:48px 0}.ck-org .ck-branches,.ck-org .ck-division-grid,.ck-org .ck-division-grid .ck-list{grid-template-columns:1fr}.ck-org .ck-panel-head{padding:20px}.ck-org .ck-body{padding-top:32px}.ck-org h2{font-size:32px}}
		</style>
		<section class="ck-org" aria-label="Organigramme de la mairie de Kafountine">
			<header class="ck-hero"><div class="ck-wrap"><p class="ck-eyebrow">Commune de Kafountine · La mairie</p><h1>Organigramme de la mairie</h1><p class="ck-intro">Découvrez l’organisation des services municipaux et les équipes au service des populations de Kafountine.</p></div></header>
			<div class="ck-wrap ck-body"><div class="ck-top"><h2>Mairie de Kafountine</h2><p>Le Maire</p><?php if ( $maire_name ) : ?><p class="ck-mayor-name"><?php echo esc_html( $maire_name ); ?></p><?php endif; ?></div><div class="ck-stem" aria-hidden="true"></div><div class="ck-branches"><?php echo $cabinet; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $secretariat; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><p class="ck-legend">Le cabinet et le secrétariat sont rattachés au Maire. Les trois divisions relèvent du secrétariat municipal.</p><section class="ck-divisions"><h2 class="ck-section-title">Les divisions municipales</h2><p class="ck-section-lead">Sous la coordination du secrétaire municipal.</p><div class="ck-division-grid"><?php echo $divisions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></section><p class="ck-footer">Les données affichées proviennent des fiches Agents et responsables et Services municipaux du portail.</p></div>
		</section>
		<?php
		return ob_get_clean();
	}

	public function admin_menu() {
		add_management_page(
			'Importateur du portail institutionnel',
			'Importateur CK',
			'manage_options',
			'ck-portail-importateur',
			array( $this, 'render_page' )
		);
	}

	private function can_run() {
		return current_user_can( 'manage_options' );
	}

	private function json_upload( $field ) {
		if ( empty( $_FILES[ $field ]['tmp_name'] ) ) {
			return array();
		}
		$file = $_FILES[ $field ];
		if ( UPLOAD_ERR_OK !== (int) $file['error'] || 'application/json' !== $file['type'] && 'text/json' !== $file['type'] && 'application/octet-stream' !== $file['type'] ) {
			throw new RuntimeException( 'Le fichier « ' . esc_html( $field ) . ' » n’est pas un JSON valide.' );
		}
		$raw = file_get_contents( $file['tmp_name'] );
		$data = json_decode( $raw, true );
		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $data ) ) {
			throw new RuntimeException( 'JSON invalide pour « ' . esc_html( $field ) . ' » : ' . json_last_error_msg() );
		}
		return $data;
	}

	private function existing_id( $key ) {
		$posts = get_posts( array(
			'post_type'      => 'any',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => self::META_KEY,
			'meta_value'     => $key,
		) );
		return empty( $posts ) ? 0 : (int) $posts[0];
	}

	private function upsert( $post_type, $title, $key, $content = '' ) {
		if ( ! post_type_exists( $post_type ) ) {
			throw new RuntimeException( 'Le type de contenu « ' . esc_html( $post_type ) . ' » n’existe pas. Importez d’abord les définitions ACF.' );
		}
		$id = $this->existing_id( $key );
		$post = array(
			'post_type'    => $post_type,
			'post_title'   => wp_strip_all_tags( $title ),
			'post_content' => wp_kses_post( $content ),
			'post_status'  => 'draft',
		);
		if ( $id ) {
			$post['ID'] = $id;
			$id = wp_update_post( wp_slash( $post ), true );
			$action = 'mis à jour';
		} else {
			$id = wp_insert_post( wp_slash( $post ), true );
			$action = 'créé';
		}
		if ( is_wp_error( $id ) ) {
			throw new RuntimeException( $id->get_error_message() );
		}
		update_post_meta( $id, self::META_KEY, $key );
		return array( (int) $id, $action );
	}

	private function field( $name, $value, $post_id ) {
		if ( null === $value || '' === $value ) {
			return;
		}
		if ( function_exists( 'update_field' ) ) {
			update_field( $name, $value, $post_id );
		} else {
			update_post_meta( $post_id, $name, $value );
		}
	}

	private function import_elus( $data, &$log ) {
		$count = 0;
		foreach ( (array) ( $data['records'] ?? array() ) as $row ) {
			$name = sanitize_text_field( $row['title'] ?? trim( ( $row['prenom'] ?? '' ) . ' ' . ( $row['nom'] ?? '' ) ) );
			if ( '' === $name ) {
				continue;
			}
			list( $id, $action ) = $this->upsert( 'ck_elu', $name, 'elu:' . (int) ( $row['ordre'] ?? 0 ), 'Source : registre des membres du Conseil municipal.' );
			$this->field( 'presentation_courte', 'Membre du Conseil municipal.', $id );
			update_post_meta( $id, '_ck_prenom_source', sanitize_text_field( $row['prenom'] ?? '' ) );
			update_post_meta( $id, '_ck_nom_source', sanitize_text_field( $row['nom'] ?? '' ) );
			if ( ! empty( $row['village_source'] ) ) {
				update_post_meta( $id, '_ck_village_source', sanitize_text_field( $row['village_source'] ) );
				$term = term_exists( $row['village_source'], 'ck_zone' );
				if ( $term ) {
					wp_set_object_terms( $id, array( (int) ( is_array( $term ) ? $term['term_id'] : $term ) ), 'ck_zone', false );
				}
			}
			$log[] = 'Élu ' . $name . ' : ' . $action . ' (ID ' . $id . ').';
			$count++;
		}
		return $count;
	}

	private function find_elu_id( $name ) {
		$ids = get_posts( array( 'post_type' => 'ck_elu', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'title' => $name ) );
		return empty( $ids ) ? 0 : (int) $ids[0];
	}

	private function import_commissions( $data, &$log ) {
		if ( 2 === (int) ( $data['schema_version'] ?? 0 ) ) {
			require_once __DIR__ . '/commissions-v2.php';
			return CK_Commissions_V2::run( $data, $log );
		}
		$count = 0;
		foreach ( (array) ( $data['records'] ?? array() ) as $row ) {
			$title = sanitize_text_field( $row['title'] ?? '' );
			if ( '' === $title ) {
				continue;
			}
			$content = 'Président : ' . ( $row['president'] ?? '' ) . "\n" . 'Vice-président : ' . ( $row['vice_president'] ?? '' ) . "\n" . 'Rapporteur : ' . ( $row['rapporteur'] ?? 'À renseigner' );
			list( $id, $action ) = $this->upsert( 'ck_commission', $title, 'commission:' . (int) ( $row['ordre'] ?? 0 ), $content );
			$this->field( 'competences', 'Commission technique du Conseil municipal.', $id );
			$composition = array();
			foreach ( array( 'president' => 'Président', 'vice_president' => 'Vice-président', 'rapporteur' => 'Rapporteur' ) as $source => $role ) {
				if ( ! empty( $row[ $source ] ) && ( $elu_id = $this->find_elu_id( $row[ $source ] ) ) ) {
					$composition[] = array( 'elu' => $elu_id, 'role' => $role, 'precision' => '' );
				}
			}
			foreach ( (array) ( $row['membres'] ?? array() ) as $member ) {
				if ( $elu_id = $this->find_elu_id( $member ) ) {
					$composition[] = array( 'elu' => $elu_id, 'role' => 'Membre', 'precision' => '' );
				}
			}
			if ( $composition ) {
				$this->field( 'composition', $composition, $id );
			}
			$log[] = 'Commission « ' . $title . ' » : ' . $action . ' (ID ' . $id . ').';
			$count++;
		}
		return $count;
	}

	private function import_personnel( $data, &$log ) {
		$count = 0;
		foreach ( (array) ( $data['records'] ?? array() ) as $row ) {
			$structure = sanitize_text_field( $row['structure'] ?? '' );
			$fonction  = sanitize_text_field( $row['fonction'] ?? '' );
			$service_id = $this->upsert( 'ck_service', $structure, 'service:' . sanitize_title( $structure ), 'Unité de l’administration communale selon la fiche d’organigramme.' );
			$service_id = (int) $service_id[0];
			foreach ( (array) ( $row['personnes'] ?? array() ) as $person ) {
				$person = sanitize_text_field( $person );
				if ( '' === $person ) {
					continue;
				}
				list( $id, $action ) = $this->upsert( 'ck_agent', $person, 'agent:' . sanitize_title( $structure . '|' . $fonction . '|' . $person ), 'Agent ou responsable public — information issue de la fiche d’organigramme.' );
				$this->field( 'fonction', $fonction, $id );
				$this->field( 'service', $service_id, $id );
				update_post_meta( $id, '_ck_structure_source', $structure );
				$log[] = 'Agent « ' . $person . ' » : ' . $action . ' (ID ' . $id . ').';
				$count++;
			}
		}
		return $count;
	}

	public function render_page() {
		if ( ! $this->can_run() ) {
			wp_die( esc_html__( 'Accès refusé.', 'ck-portail' ) );
		}
		$log = array();
		if ( isset( $_POST['ck_import_submit'] ) ) {
			check_admin_referer( 'ck_import_json', 'ck_import_nonce' );
			try {
				$elus = $this->json_upload( 'elus' );
				$commissions = $this->json_upload( 'commissions' );
				$personnel = $this->json_upload( 'personnel' );
				$totals = array();
				if ( $elus ) { $totals['elus'] = $this->import_elus( $elus, $log ); }
				if ( $commissions ) { $totals['commissions'] = $this->import_commissions( $commissions, $log ); }
				if ( $personnel ) { $totals['personnel'] = $this->import_personnel( $personnel, $log ); }
				printf( '<div class="notice notice-success"><p>Import terminé : %s.</p></div>', esc_html( implode( ', ', array_map( function( $key, $value ) { return $value . ' ' . $key; }, array_keys( $totals ), $totals ) ) ?: 'aucun fichier sélectionné' ) );
			} catch ( Throwable $error ) {
				printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $error->getMessage() ) );
			}
		}
		?>
		<div class="wrap">
			<h1>Importateur du portail institutionnel</h1>
			<p>Les fiches sont créées en brouillon et peuvent être relancées sans doublons.</p>
			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'ck_import_json', 'ck_import_nonce' ); ?>
				<table class="form-table" role="presentation">
					<tr><th><label for="elus">Élus municipaux</label></th><td><input id="elus" name="elus" type="file" accept=".json,application/json"></td></tr>
					<tr><th><label for="commissions">Commissions municipales</label></th><td><input id="commissions" name="commissions" type="file" accept=".json,application/json"></td></tr>
					<tr><th><label for="personnel">Personnel administratif</label></th><td><input id="personnel" name="personnel" type="file" accept=".json,application/json"></td></tr>
				</table>
				<?php submit_button( 'Importer les fichiers sélectionnés', 'primary', 'ck_import_submit' ); ?>
			</form>
			<?php if ( $log ) : ?><h2>Résultat</h2><textarea readonly style="width:100%;height:260px"><?php echo esc_textarea( implode( "\n", $log ) ); ?></textarea><?php endif; ?>
		</div>
		<?php
	}
}

new CK_Portail_Importateur();
