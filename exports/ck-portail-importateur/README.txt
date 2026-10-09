CK Portail institutionnel — Importateur

Installation
1. Compresser le dossier `ck-portail-importateur` en ZIP.
2. Dans WordPress, ouvrir Extensions > Ajouter > Téléverser une extension.
3. Téléverser le ZIP, installer puis activer l’extension.
4. Ouvrir Outils > Importateur CK.

Ordre
1. Importer d’abord les types de contenus, taxonomies et pages d’options avec ACF.
2. Importer les 19 villages avec l’importateur WordPress.
3. Utiliser cette page pour sélectionner les trois fichiers JSON de données.

Fichiers JSON
- donnees-elus-municipaux.json
- donnees-commissions-municipales.json
- donnees-personnel-administratif.json

Les fiches sont créées en brouillon. Une nouvelle exécution met à jour les fiches créées par cet importeur grâce à la clé interne `_ck_import_key`.

Commissions — format version 2 (extension 1.2.0)
Utiliser commissions/donnees-commissions.json après import des définitions ACF du même dossier.
ACF Pro et le groupe de champs de composition sont requis.
Les nouvelles commissions sont créées en brouillon ; les commissions existantes sont conservées sans modification.
Les identités sans correspondance unique sont conservées dans nom_source avec le statut « Identité à confirmer ».
Aucun élu ni aucune photographie ne sont créés automatiquement par cet import.
Consulter commissions/README.md pour les détails d’installation et les limites.
