"""Static contract checks for the WordPress extension (no WordPress runtime)."""
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parent
PACKAGE = ROOT / "ck-admin-communal"
PLUGIN = (PACKAGE / "ck-admin-communal.php").read_text(encoding="utf-8")
ADMIN_CSS = (PACKAGE / "assets/admin.css").read_text(encoding="utf-8")
ARCHIVE = ROOT / "ck-admin-communal.zip"

assert "add_filter( 'login_title'" in PLUGIN
assert "Espace d’administration — Commune de Kafountine" in PLUGIN
assert "$wp_admin_bar->remove_node( 'wp-logo' );" in PLUGIN
assert "'profile.php'" in PLUGIN
assert "remove_submenu_page( 'profile.php'" not in PLUGIN
assert "manage_options" in PLUGIN and "manage_network" in PLUGIN
assert "register_rest_route" in PLUGIN
assert "'/portal-settings'" in PLUGIN
assert "permission_callback" in PLUGIN
assert "'adresse'" in PLUGIN
assert "'email', 'option'" in PLUGIN
assert "Version: 2.2.0" in PLUGIN
assert "ck_admin_communal_register_roles" in PLUGIN
assert "register_activation_hook" in PLUGIN
assert "add_role( 'ck_gestionnaire'" in PLUGIN
assert "Gestionnaire communal" in PLUGIN
assert "'edit_posts'" in PLUGIN
assert "'upload_files'" in PLUGIN
assert "'manage_categories'" in PLUGIN
assert "'delete_posts'      => false" in PLUGIN
assert "'publish_posts'     => false" in PLUGIN
assert "ck_admin_communal_roles_version" in PLUGIN
assert "delete_option( 'ck_admin_communal_roles_version' )" in (PACKAGE / "uninstall.php").read_text(encoding="utf-8")
assert "ck_admin_communal_dashboard_counts" in PLUGIN
assert "ck-communal-dashboard" in PLUGIN
assert "ESPACE ÉDITORIAL COMMUNAL" in PLUGIN
assert "fiches villages" in PLUGIN
assert ".ck-dashboard-hero" in ADMIN_CSS
assert ".ck-dashboard-stats" in ADMIN_CSS
assert ".ck-dashboard-card" in ADMIN_CSS
assert "ck-dashboard-review" in PLUGIN
assert "À traiter en priorité" in PLUGIN
assert "ck-dashboard-quick-actions" in PLUGIN
assert ".ck-dashboard-quick-actions" in ADMIN_CSS
assert ".ck-dashboard-review" in ADMIN_CSS
assert ".ck-dashboard-card-primary h2" in ADMIN_CSS
assert "ck_admin_communal_redirect_to_home" in PLUGIN
assert "add_action( 'admin_init', 'ck_admin_communal_redirect_to_home' )" in PLUGIN
assert "ck_admin_communal_login_redirect" in PLUGIN
assert "add_filter( 'login_redirect', 'ck_admin_communal_login_redirect'" in PLUGIN
assert "ck_admin_communal_portal_url()" in PLUGIN
assert "remove_node( 'site-name' )" in PLUGIN
assert "'id'    => 'ck-portal-public'" in PLUGIN
assert "'title' => 'Portail public'" in PLUGIN
assert "add_submenu_page" in PLUGIN
assert "business_duplicate_menus" in PLUGIN
assert "'edit.php?post_type=ck_service'" in PLUGIN
assert "post-new.php?post_type=ck_demarche" in PLUGIN
assert "Nouvelle démarche" in PLUGIN
assert "Nouveau document" in PLUGIN
assert "ck_admin_communal_add_editorial_guidance" in PLUGIN
assert "Source officielle et date de vérification" in PLUGIN
assert "Référence ou source administrative" in PLUGIN
assert "Documents justificatifs et date de mise à jour" in PLUGIN
assert "wp_insert_post_data" in PLUGIN
assert "'pending'" in PLUGIN
assert "add_role( 'ck_editeur'" in PLUGIN
assert "Éditeur communal" in PLUGIN
assert "'publish_posts'     => true" in PLUGIN
assert "ck_admin_communal_review_page" in PLUGIN
assert "ck-admin-communal-review" in PLUGIN
assert "À relire" in PLUGIN
assert "post_status'    => 'pending'" in PLUGIN
assert "'callback' => 'ck_admin_communal_review_page'" in PLUGIN
assert "get_posts(" in PLUGIN
assert "Ouvrir la fiche" in PLUGIN
assert "admin.php?page=ck-admin-communal-review" in PLUGIN

with ZipFile(ARCHIVE) as archive:
    names = set(archive.namelist())
    required = {
        "ck-admin-communal/ck-admin-communal.php",
        "ck-admin-communal/assets/admin.css",
        "ck-admin-communal/assets/login.css",
        "ck-admin-communal/assets/logo-portailKafountine.png",
        "ck-admin-communal/uninstall.php",
    }
    assert required <= names

print("Contrats statiques CK Admin communal : OK")