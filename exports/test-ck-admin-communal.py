"""Static contract checks for the WordPress extension (no WordPress runtime)."""
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parent
PACKAGE = ROOT / "ck-admin-communal"
PLUGIN = (PACKAGE / "ck-admin-communal.php").read_text(encoding="utf-8")
ARCHIVE = ROOT / "ck-admin-communal.zip"

assert "add_filter( 'login_title'" in PLUGIN
assert "Espace d’administration — Commune de Kafountine" in PLUGIN
assert "$wp_admin_bar->remove_node( 'wp-logo' );" in PLUGIN
assert "'profile.php'" in PLUGIN
assert "remove_submenu_page( 'profile.php'" not in PLUGIN
assert "manage_options" in PLUGIN and "manage_network" in PLUGIN

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