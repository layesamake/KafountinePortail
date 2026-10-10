<?php
/**
 * Plugin Name: CK Admin communal
 * Description: Identité et navigation sécurisée du back-office de la Commune de Kafountine.
 * Version: 1.2.0
 * Author: Commune de Kafountine
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'CK_ADMIN_COMMUNAL_VERSION', '1.2.0' );
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
	$site = $wp_admin_bar->get_node( 'site-name' );
	if ( $site ) {
		$site->title = 'Commune de Kafountine';
		$site->href  = ck_admin_communal_portal_url();
		$wp_admin_bar->add_node( $site );
	}
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

function ck_admin_communal_navigation_page() {
	$items = ck_admin_communal_navigation_items();
	?>
	<div class="wrap ck-communal-navigation">
		<h1>Commune de Kafountine</h1>
		<p>Accès rapide aux espaces de gestion autorisés pour votre profil.</p>
		<nav aria-label="Navigation communale">
			<ul>
				<?php foreach ( $items as $item ) : ?>
					<li><a class="button button-secondary" href="<?php echo esc_url( admin_url( $item['url'] ) ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
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
