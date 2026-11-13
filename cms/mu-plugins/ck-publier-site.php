<?php
/**
 * Plugin Name: CK – Publication immédiate du portail
 * Description: À chaque modification de contenu, déclenche la reconstruction du site officiel (GitHub Actions).
 * Configuration : dans wp-config.php, ajouter
 *   define('CK_GITHUB_TOKEN', 'github_pat_...');   // jeton à accès limité : Actions (écriture) sur KafountinePortail
 */
if (!defined('ABSPATH')) exit;

const CK_REPO  = 'layesamake/KafountinePortail';
const CK_HOOK  = 'ck_declencher_publication';

function ck_planifier_publication() {
    // Regroupe les enregistrements rapprochés : un seul déploiement 20 s après la dernière modification.
    if (!defined('CK_GITHUB_TOKEN')) return;
    wp_clear_scheduled_hook(CK_HOOK);
    wp_schedule_single_event(time() + 20, CK_HOOK);
}

function ck_type_concerne($post) {
    $post = get_post($post);
    if (!$post) return false;
    return strpos($post->post_type, 'ck_') === 0 || in_array($post->post_type, ['actualites', 'post', 'page'], true);
}

add_action('save_post', function ($id, $post) {
    if (wp_is_post_revision($id) || wp_is_post_autosave($id)) return;
    if (ck_type_concerne($post)) ck_planifier_publication();
}, 20, 2);
add_action('before_delete_post', function ($id) { if (ck_type_concerne($id)) ck_planifier_publication(); });
add_action('trashed_post',       function ($id) { if (ck_type_concerne($id)) ck_planifier_publication(); });
add_action('untrashed_post',     function ($id) { if (ck_type_concerne($id)) ck_planifier_publication(); });
foreach (['created_term', 'edited_term', 'delete_term'] as $h) {
    add_action($h, function ($a, $b = 0, $taxonomy = '') {
        $tax = is_string($taxonomy) && $taxonomy ? $taxonomy : (is_string($b) ? $b : '');
        if (strpos((string) $tax, 'ck_') === 0) ck_planifier_publication();
    }, 20, 3);
}
add_action('acf/save_post', function ($id) {
    if (is_string($id) && strpos($id, 'options') !== false) ck_planifier_publication();
}, 20);

add_action(CK_HOOK, function () {
    if (!defined('CK_GITHUB_TOKEN')) return;
    $r = wp_remote_post('https://api.github.com/repos/' . CK_REPO . '/dispatches', [
        'timeout' => 15,
        'headers' => [
            'Authorization' => 'Bearer ' . CK_GITHUB_TOKEN,
            'Accept'        => 'application/vnd.github+json',
            'User-Agent'    => 'ck-publier-site',
            'Content-Type'  => 'application/json',
        ],
        'body' => wp_json_encode(['event_type' => 'cms-update']),
    ]);
    if (is_wp_error($r) || wp_remote_retrieve_response_code($r) >= 300) {
        error_log('CK publication: échec ' . (is_wp_error($r) ? $r->get_error_message() : wp_remote_retrieve_response_code($r)));
    }
});
