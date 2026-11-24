<?php
/**
 * Code Snippets Pro — à exécuter une fois (« Exécuter une fois »).
 * Crée les termes des formulaires Projet municipal s'ils n'existent pas.
 */
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
foreach ( $termes as $taxonomie => $noms ) {
	if ( ! taxonomy_exists( $taxonomie ) ) { continue; }
	foreach ( $noms as $nom ) {
		if ( ! term_exists( $nom, $taxonomie ) ) { wp_insert_term( $nom, $taxonomie ); }
	}
}
