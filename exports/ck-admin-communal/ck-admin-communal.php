<?php
/**
 * Plugin Name: CK Admin communal
 * Description: Identité et navigation sécurisée du back-office de la Commune de Kafountine.
 * Version: 1.9.0
 * Author: Commune de Kafountine
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'CK_ADMIN_COMMUNAL_VERSION', '1.9.0' );
define( 'CK_ADMIN_COMMUNAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'CK_ADMIN_COMMUNAL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Register the least-privileged editorial role used for communal data entry.
 * The role can prepare and submit content, but cannot publish or delete it.
 */
function ck_admin_communal_register_roles() {
	$capabilities = array(
		'read'             => true,
		'edit_posts'       => true,
		'edit_others_posts' => true,
		'edit_published_posts' => false,
		'publish_posts'     => false,
		'delete_posts'      => false,
		'delete_others_posts' => false,
		'delete_published_posts' => false,
		'upload_files'      => true,
		'assign_categories' => true,
		'manage_categories' => true,
	);

	$role = get_role( 'ck_gestionnaire' );
	if ( ! $role ) {
		add_role( 'ck_gestionnaire', 'Gestionnaire communal', $capabilities );
		return;
	}

	foreach ( $capabilities as $capability => $grant ) {
		if ( $grant ) {
			$role->add_cap( $capability );
		} else {
			$role->remove_cap( $capability );
		}
	}
}
register_activation_hook( __FILE__, 'ck_admin_communal_register_roles' );

/**
 * Keep the role synchronized after plugin updates without changing users.
 */
function ck_admin_communal_sync_roles() {
	if ( get_option( 'ck_admin_communal_roles_version' ) !== CK_ADMIN_COMMUNAL_VERSION ) {
		ck_admin_communal_register_roles();
		update_option( 'ck_admin_communal_roles_version', CK_ADMIN_COMMUNAL_VERSION, false );
	}
}
add_action( 'plugins_loaded', 'ck_admin_communal_sync_roles' );

/**
 * Expose only the public municipal settings consumed by the static portal.
 * Private editorial fields and contacts are intentionally excluded.
 */
function ck_admin_communal_portal_settings() {
	$hours = function_exists( 'get_field' ) ? get_field( 'horaires', 'option' ) : array();
	$formatted_hours = array();

	if ( is_array( $hours ) ) {
		foreach ( $hours as $entry ) {
			$day = sanitize_text_field( $entry['jour'] ?? '' );
			$opening = sanitize_text_field( $entry['ouverture'] ?? '' );
			$closing = sanitize_text_field( $entry['fermeture'] ?? '' );
			if ( $day && $opening && $closing ) {
				$formatted_hours[] = array(
					'day'   => $day,
					'value' => $opening . ' — ' . $closing,
				);
			}
		}
	}

	$read_option = static function ( $field, $fallback = '' ) {
		$value = function_exists( 'get_field' ) ? get_field( $field, 'option' ) : '';
		return is_string( $value ) && trim( $value ) !== '' ? sanitize_textarea_field( $value ) : $fallback;
	};

	return rest_ensure_response(
		array(
			'denomination'  => $read_option( 'denomination', 'Commune de Kafountine' ),
			'presentation'  => $read_option( 'presentation' ),
			'region'        => $read_option( 'region' ),
			'departement'   => $read_option( 'departement' ),
			'arrondissement' => $read_option( 'arrondissement' ),
			'address'       => $read_option( 'adresse' ),
			'telephone'     => $read_option( 'telephone' ),
			'email'         => function_exists( 'get_field' ) ? sanitize_email( (string) get_field( 'email', 'option' ) ) : '',
			'hours'         => $formatted_hours,
		)
	);
}

function ck_admin_communal_register_portal_routes() {
	register_rest_route(
		'ck/v1',
		'/portal-settings',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'ck_admin_communal_portal_settings',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'ck_admin_communal_register_portal_routes' );

/**
 * Return true for technical administrators who must retain the complete UI.
 * Capabilities are checked at runtime; roles are deliberately not hard-coded.
 */
function ck_admin_communal_is_technical_user() {
	return current_user_can( 'manage_options' ) || current_user_can( 'manage_network' );
}

function ck_admin_communal_portal_url() {
	return apply_filters( 'ck_admin_communal_portal_url', 'https://communekafountine.com/' );
}

function ck_admin_communal_enqueue_admin_assets() {
	if ( ! is_admin() ) {
		return;
	}

	wp_enqueue_style(
		'ck-admin-communal',
		CK_ADMIN_COMMUNAL_URL . 'assets/admin.css',
		array(),
		CK_ADMIN_COMMUNAL_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'ck_admin_communal_enqueue_admin_assets' );

function ck_admin_communal_enqueue_login_assets() {
	wp_enqueue_style(
		'ck-admin-communal-login',
		CK_ADMIN_COMMUNAL_URL . 'assets/login.css',
		array(),
		CK_ADMIN_COMMUNAL_VERSION
	);
}
add_action( 'login_enqueue_scripts', 'ck_admin_communal_enqueue_login_assets' );

function ck_admin_communal_login_url() {
	return esc_url( ck_admin_communal_portal_url() );
}
add_filter( 'login_headerurl', 'ck_admin_communal_login_url' );

function ck_admin_communal_login_title() {
	return 'Espace d’administration';
}
add_filter( 'login_headertext', 'ck_admin_communal_login_title' );

function ck_admin_communal_document_title( $login_title, $login_header ) {
	return 'Espace d’administration — Commune de Kafountine';
}
add_filter( 'login_title', 'ck_admin_communal_document_title', 10, 2 );

function ck_admin_communal_login_message( $message ) {
	$message .= '<p class="ck-login-subtitle">Portail officiel de la Commune de Kafountine</p>';
	return $message;
}
add_filter( 'login_message', 'ck_admin_communal_login_message' );

function ck_admin_communal_admin_title( $admin_title, $title ) {
	return $title . ' ‹ Commune de Kafountine — Administration';
}
add_filter( 'admin_title', 'ck_admin_communal_admin_title', 10, 2 );

function ck_admin_communal_footer_text( $text ) {
	if ( ck_admin_communal_is_technical_user() ) {
		return $text;
	}
	return 'Commune de Kafountine · Administration municipale';
}
add_filter( 'admin_footer_text', 'ck_admin_communal_footer_text' );

function ck_admin_communal_update_footer( $text ) {
	if ( ck_admin_communal_is_technical_user() ) {
		return $text;
	}
	return 'Portail officiel de la Commune de Kafountine';
}
add_filter( 'update_footer', 'ck_admin_communal_update_footer', 10, 1 );

function ck_admin_communal_brand_admin_bar( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'site-name' );
	$wp_admin_bar->add_node(
		array(
			'id'    => 'ck-portal-public',
			'title' => 'Portail public',
			'href'  => ck_admin_communal_portal_url(),
			'meta'  => array(
				'target' => '_blank',
				'rel'    => 'noopener',
			),
		)
	);

	if ( ! ck_admin_communal_is_technical_user() ) {
		$wp_admin_bar->remove_node( 'wp-logo' );
	}
}
add_action( 'admin_bar_menu', 'ck_admin_communal_brand_admin_bar', 80 );

function ck_admin_communal_navigation_items() {
	$items = array(
		array( 'label' => 'Accueil', 'url' => 'index.php', 'capability' => 'read', 'available' => true ),
		array( 'label' => 'Publications', 'url' => 'edit.php', 'capability' => 'edit_posts', 'available' => post_type_exists( 'post' ) ),
		array( 'label' => 'Services aux citoyens', 'url' => 'edit.php?post_type=ck_service', 'capability' => 'edit_posts', 'available' => post_type_exists( 'ck_service' ) ),
		array( 'label' => 'La Commune', 'url' => 'edit.php?post_type=page', 'capability' => 'edit_pages', 'available' => post_type_exists( 'page' ) ),
		array( 'label' => 'Territoire', 'url' => 'edit-tags.php?taxonomy=ck_zone', 'capability' => 'manage_categories', 'available' => taxonomy_exists( 'ck_zone' ) ),
		array( 'label' => 'Projets et transparence', 'url' => 'edit.php?post_type=ck_projet', 'capability' => 'edit_posts', 'available' => post_type_exists( 'ck_projet' ) ),
		array( 'label' => 'Médiathèque', 'url' => 'upload.php', 'capability' => 'upload_files', 'available' => true ),
		array( 'label' => 'Contributions', 'url' => 'edit-comments.php', 'capability' => 'moderate_comments', 'available' => true ),
		array( 'label' => 'Administration', 'url' => 'profile.php', 'capability' => 'read', 'available' => true ),
	);

	return array_values( array_filter( $items, function ( $item ) {
		return $item['available'] && current_user_can( $item['capability'] );
	} ) );
}

/**
 * Open the communal dashboard instead of the generic WordPress dashboard.
 * Technical administrators keep the native WordPress start page.
 */
function ck_admin_communal_redirect_to_home() {
	if ( ! is_admin() || wp_doing_ajax() || ck_admin_communal_is_technical_user() ) {
		return;
	}

	global $pagenow;
	if ( 'index.php' !== $pagenow || ! current_user_can( 'read' ) ) {
		return;
	}

	wp_safe_redirect( admin_url( 'admin.php?page=ck-admin-communal-home' ) );
	exit;
}
add_action( 'admin_init', 'ck_admin_communal_redirect_to_home' );

/**
 * Send editorial profiles directly to the communal dashboard after login.
 */
function ck_admin_communal_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
	if ( is_wp_error( $user ) || ! $user || user_can( $user, 'manage_options' ) || user_can( $user, 'manage_network' ) ) {
		return $redirect_to;
	}

	return admin_url( 'admin.php?page=ck-admin-communal-home' );
}
add_filter( 'login_redirect', 'ck_admin_communal_login_redirect', 10, 3 );

function ck_admin_communal_navigation_menu() {
	add_menu_page(
		'Accueil communal',
		'Accueil',
		'read',
		'ck-admin-communal-home',
		'ck_admin_communal_navigation_page',
		'dashicons-building',
		2
	);
}
add_action( 'admin_menu', 'ck_admin_communal_navigation_menu', 20 );

function ck_admin_communal_register_navigation_submenus() {
	$items = array(
		array( 'title' => 'Services aux citoyens', 'menu' => 'Services', 'capability' => 'edit_posts', 'slug' => 'edit.php?post_type=ck_service', 'available' => post_type_exists( 'ck_service' ) ),
		array( 'title' => 'Territoire communal', 'menu' => 'Territoire', 'capability' => 'manage_categories', 'slug' => 'edit-tags.php?taxonomy=ck_zone', 'available' => taxonomy_exists( 'ck_zone' ) ),
		array( 'title' => 'Élus et conseil', 'menu' => 'Élus', 'capability' => 'edit_posts', 'slug' => 'edit.php?post_type=ck_elu', 'available' => post_type_exists( 'ck_elu' ) ),
		array( 'title' => 'Commissions', 'menu' => 'Commissions', 'capability' => 'edit_posts', 'slug' => 'edit.php?post_type=ck_commission', 'available' => post_type_exists( 'ck_commission' ) ),
		array( 'title' => 'Agents communaux', 'menu' => 'Agents', 'capability' => 'edit_posts', 'slug' => 'edit.php?post_type=ck_agent', 'available' => post_type_exists( 'ck_agent' ) ),
		array( 'title' => 'Démarches administratives', 'menu' => 'Démarches', 'capability' => 'edit_posts', 'slug' => 'edit.php?post_type=ck_demarche', 'available' => post_type_exists( 'ck_demarche' ) ),
		array( 'title' => 'Documents publics', 'menu' => 'Documents', 'capability' => 'edit_posts', 'slug' => 'edit.php?post_type=ck_document', 'available' => post_type_exists( 'ck_document' ) ),
		array( 'title' => 'Projets et transparence', 'menu' => 'Projets', 'capability' => 'edit_posts', 'slug' => 'edit.php?post_type=ck_projet', 'available' => post_type_exists( 'ck_projet' ) ),
		array( 'title' => 'Nouvelle démarche', 'menu' => 'Ajouter une démarche', 'capability' => 'edit_posts', 'slug' => 'post-new.php?post_type=ck_demarche', 'available' => post_type_exists( 'ck_demarche' ) ),
		array( 'title' => 'Nouveau document', 'menu' => 'Ajouter un document', 'capability' => 'edit_posts', 'slug' => 'post-new.php?post_type=ck_document', 'available' => post_type_exists( 'ck_document' ) ),
		array( 'title' => 'Nouveau projet', 'menu' => 'Ajouter un projet', 'capability' => 'edit_posts', 'slug' => 'post-new.php?post_type=ck_projet', 'available' => post_type_exists( 'ck_projet' ) ),
		array( 'title' => 'Médiathèque', 'menu' => 'Médiathèque', 'capability' => 'upload_files', 'slug' => 'upload.php', 'available' => true ),
		array( 'title' => 'Mon profil', 'menu' => 'Mon profil', 'capability' => 'read', 'slug' => 'profile.php', 'available' => true ),
	);

	foreach ( $items as $item ) {
		if ( $item['available'] && current_user_can( $item['capability'] ) ) {
			add_submenu_page( 'ck-admin-communal-home', $item['title'], $item['menu'], $item['capability'], $item['slug'] );
		}
	}
}
add_action( 'admin_menu', 'ck_admin_communal_register_navigation_submenus', 21 );

function ck_admin_communal_editorial_post_types() {
	return array_values(
		array_filter(
			array( 'ck_demarche', 'ck_document', 'ck_projet' ),
			'post_type_exists'
		)
	);
}

function ck_admin_communal_add_editorial_guidance() {
	foreach ( ck_admin_communal_editorial_post_types() as $post_type ) {
		add_meta_box(
			'ck-editorial-guide-' . $post_type,
			'Guide de saisie communale',
			'ck_admin_communal_render_editorial_guidance',
			$post_type,
			'side',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'ck_admin_communal_add_editorial_guidance' );

function ck_admin_communal_render_editorial_guidance( $post ) {
	$guidance = array(
		'ck_demarche' => array(
			'title' => 'Démarche administrative',
			'items' => array( 'Titre compréhensible pour les citoyens', 'Pièces à fournir et conditions', 'Délais, coût et service responsable' ),
		),
		'ck_document' => array(
			'title' => 'Document public',
			'items' => array( 'Titre et catégorie du document', 'Date officielle et description', 'Fichier lisible et correctement nommé' ),
		),
		'ck_projet' => array(
			'title' => 'Projet municipal',
			'items' => array( 'Objectif et description du projet', 'Zone ou village concerné', 'État d’avancement et calendrier' ),
		),
	);
	$current = $guidance[ $post->post_type ] ?? array( 'title' => 'Contenu communal', 'items' => array() );
	?>
	<p><strong><?php echo esc_html( $current['title'] ); ?></strong></p>
	<ul>
		<?php foreach ( $current['items'] as $item ) : ?>
			<li>☐ <?php echo esc_html( $item ); ?></li>
		<?php endforeach; ?>
	</ul>
	<p><em>Enregistrez en brouillon, puis utilisez « Envoyer pour relecture » lorsque les informations sont vérifiées.</em></p>
	<?php
}

function ck_admin_communal_force_review_status( $data, $postarr ) {
	if ( ck_admin_communal_is_technical_user() || ! in_array( $data['post_type'] ?? '', ck_admin_communal_editorial_post_types(), true ) ) {
		return $data;
	}

	if ( 'publish' === ( $data['post_status'] ?? '' ) && ! current_user_can( 'publish_posts' ) ) {
		$data['post_status'] = 'pending';
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'ck_admin_communal_force_review_status', 10, 2 );

function ck_admin_communal_dashboard_counts() {
	$zone_count = taxonomy_exists( 'ck_zone' ) ? wp_count_terms( array( 'taxonomy' => 'ck_zone', 'hide_empty' => false ) ) : 0;
	$counts = array(
		'zones'       => is_wp_error( $zone_count ) ? 0 : (int) $zone_count,
		'services'    => 0,
		'elu'         => 0,
		'commissions' => 0,
		'agents'      => 0,
		'demarches'   => 0,
		'documents'   => 0,
		'projets'     => 0,
		'pending'     => 0,
	);

	$post_types = array(
		'services'    => 'ck_service',
		'elu'         => 'ck_elu',
		'commissions' => 'ck_commission',
		'agents'      => 'ck_agent',
		'demarches'   => 'ck_demarche',
		'documents'   => 'ck_document',
		'projets'     => 'ck_projet',
	);

	foreach ( $post_types as $key => $post_type ) {
		if ( ! post_type_exists( $post_type ) ) {
			continue;
		}
		$counts_for_type = wp_count_posts( $post_type );
		$counts[ $key ] = (int) ( $counts_for_type->publish ?? 0 );
		$counts['pending'] += (int) ( $counts_for_type->pending ?? 0 ) + (int) ( $counts_for_type->draft ?? 0 );
	}

	return $counts;
}

function ck_admin_communal_dashboard_link( $label, $url, $class = 'button button-secondary' ) {
	return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( admin_url( $url ) ) . '">' . esc_html( $label ) . '</a>';
}

function ck_admin_communal_navigation_page() {
	$counts = ck_admin_communal_dashboard_counts();
	$user = wp_get_current_user();
	$display_name = $user->display_name ? $user->display_name : $user->user_login;
	?>
	<div class="wrap ck-communal-dashboard" id="ck-communal-dashboard">
		<section class="ck-dashboard-hero" aria-labelledby="ck-dashboard-title">
			<div>
				<p class="ck-dashboard-eyebrow">ESPACE ÉDITORIAL COMMUNAL</p>
				<h1 id="ck-dashboard-title">Bonjour, <?php echo esc_html( $display_name ); ?></h1>
				<p>Pilotez les informations qui font vivre la Commune de Kafountine.</p>
			</div>
			<div class="ck-dashboard-hero-actions">
				<a class="button button-secondary" href="<?php echo esc_url( ck_admin_communal_portal_url() ); ?>">Voir le portail public</a>
			</div>
		</section>

		<section class="ck-dashboard-stats" aria-label="État des contenus communaux">
			<?php
			$stats = array(
				array( 'label' => 'fiches villages', 'value' => $counts['zones'], 'url' => 'edit-tags.php?taxonomy=ck_zone' ),
				array( 'label' => 'services municipaux', 'value' => $counts['services'], 'url' => 'edit.php?post_type=ck_service' ),
				array( 'label' => 'contenus à relire', 'value' => $counts['pending'], 'url' => 'edit.php?post_status=pending' ),
				array( 'label' => 'documents publics', 'value' => $counts['documents'], 'url' => 'edit.php?post_type=ck_document' ),
			);
			foreach ( $stats as $stat ) :
				?>
				<a class="ck-dashboard-stat" href="<?php echo esc_url( admin_url( $stat['url'] ) ); ?>">
					<strong><?php echo esc_html( number_format_i18n( $stat['value'] ) ); ?></strong>
					<span><?php echo esc_html( $stat['label'] ); ?></span>
				</a>
				<?php
			endforeach;
			?>
		</section>

		<section class="ck-dashboard-review" aria-labelledby="ck-dashboard-review-title">
			<div>
				<p class="ck-dashboard-card-kicker">SUIVI ÉDITORIAL</p>
				<h2 id="ck-dashboard-review-title">À traiter en priorité</h2>
				<p>Les contenus enregistrés en brouillon ou envoyés pour relecture apparaîtront ici.</p>
				<?php if ( $counts['pending'] > 0 ) : ?>
					<p class="ck-dashboard-review-status"><strong><?php echo esc_html( number_format_i18n( $counts['pending'] ) ); ?></strong> contenu(s) nécessitent votre attention.</p>
				<?php else : ?>
					<p class="ck-dashboard-review-status">Aucun contenu en attente pour le moment.</p>
				<?php endif; ?>
			</div>
			<?php if ( $counts['pending'] > 0 && current_user_can( 'edit_posts' ) ) : ?>
				<?php echo ck_admin_communal_dashboard_link( 'Ouvrir les contenus à relire', 'edit.php?post_status=pending', 'button button-primary' ); ?>
			<?php endif; ?>
		</section>

		<section class="ck-dashboard-quick-actions" aria-labelledby="ck-dashboard-actions-title">
			<div class="ck-dashboard-section-heading">
				<div>
					<p class="ck-dashboard-card-kicker">ACCÈS RAPIDES</p>
					<h2 id="ck-dashboard-actions-title">Gérer les informations communales</h2>
				</div>
			</div>
			<div class="ck-dashboard-grid">
				<article class="ck-dashboard-card ck-dashboard-card-primary">
					<h3>Préparer un contenu</h3>
					<p>Ajoutez une démarche ou un projet, puis transmettez-le pour relecture.</p>
					<div class="ck-dashboard-actions">
						<?php if ( current_user_can( 'edit_posts' ) && post_type_exists( 'ck_demarche' ) ) : ?>
							<?php echo ck_admin_communal_dashboard_link( 'Ajouter une démarche', 'post-new.php?post_type=ck_demarche', 'button button-primary' ); ?>
						<?php endif; ?>
						<?php if ( current_user_can( 'edit_posts' ) && post_type_exists( 'ck_projet' ) ) : ?>
							<?php echo ck_admin_communal_dashboard_link( 'Ajouter un projet', 'post-new.php?post_type=ck_projet', 'button button-secondary' ); ?>
						<?php endif; ?>
					</div>
				</article>

				<article class="ck-dashboard-card">
					<p class="ck-dashboard-card-kicker">TERRITOIRE</p>
					<h3>Villages et zones</h3>
					<p>Complétez les informations des termes de la taxonomie ck_zone.</p>
					<?php if ( current_user_can( 'manage_categories' ) && taxonomy_exists( 'ck_zone' ) ) : ?>
						<?php echo ck_admin_communal_dashboard_link( 'Gérer les zones', 'edit-tags.php?taxonomy=ck_zone', 'button button-primary' ); ?>
					<?php endif; ?>
				</article>

				<article class="ck-dashboard-card">
					<p class="ck-dashboard-card-kicker">ORGANISATION</p>
					<h3>Registres communaux</h3>
					<p>Retrouvez les services, élus, commissions et agents selon vos droits.</p>
					<div class="ck-dashboard-actions">
						<?php if ( current_user_can( 'edit_posts' ) && post_type_exists( 'ck_service' ) ) : ?>
							<?php echo ck_admin_communal_dashboard_link( 'Services aux citoyens', 'edit.php?post_type=ck_service', 'button button-secondary' ); ?>
						<?php endif; ?>
						<?php echo ck_admin_communal_dashboard_link( 'Mon profil', 'profile.php', 'button button-secondary' ); ?>
					</div>
				</article>
			</div>
		</section>
	</div>
	<?php
}

/**
 * Remove technical-only destinations from métier profiles at the menu API level.
 * This is intentionally not CSS-only: direct URLs remain protected by capabilities.
 */
function ck_admin_communal_restrict_business_navigation() {
	if ( ck_admin_communal_is_technical_user() ) {
		return;
	}

	$technical_menus = array(
		'plugins.php',
		'themes.php',
		'options-general.php',
		'tools.php',
		'users.php',
		'edit-comments.php',
		'update-core.php',
	);
	foreach ( $technical_menus as $menu_slug ) {
		remove_menu_page( $menu_slug );
	}

	$business_duplicate_menus = array(
		'edit.php?post_type=ck_service',
		'edit.php?post_type=ck_elu',
		'edit.php?post_type=ck_commission',
		'edit.php?post_type=ck_agent',
		'edit.php?post_type=ck_demarche',
		'edit.php?post_type=ck_document',
		'edit.php?post_type=ck_projet',
		'upload.php',
	);
	foreach ( $business_duplicate_menus as $menu_slug ) {
		remove_menu_page( $menu_slug );
	}

	remove_submenu_page( 'index.php', 'update-core.php' );
}
add_action( 'admin_menu', 'ck_admin_communal_restrict_business_navigation', 999 );

function ck_admin_communal_restrict_dashboard() {
	if ( ck_admin_communal_is_technical_user() ) {
		return;
	}

	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
}
add_action( 'wp_dashboard_setup', 'ck_admin_communal_restrict_dashboard', 999 );

function ck_admin_communal_login_body_class( $classes ) {
	$classes[] = 'ck-admin-communal-login';
	return $classes;
}
add_filter( 'login_body_class', 'ck_admin_communal_login_body_class' );

function ck_admin_communal_admin_body_class( $classes ) {
	$classes .= ' ck-admin-communal';
	if ( ! ck_admin_communal_is_technical_user() ) {
		$classes .= ' ck-admin-communal-business';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'ck_admin_communal_admin_body_class' );

function ck_admin_communal_plugin_row_meta( $links, $file ) {
	if ( plugin_basename( __FILE__ ) === $file ) {
		$links[] = '<span>Portail officiel : <a href="' . esc_url( ck_admin_communal_portal_url() ) . '">communekafountine.com</a></span>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'ck_admin_communal_plugin_row_meta', 10, 2 );
