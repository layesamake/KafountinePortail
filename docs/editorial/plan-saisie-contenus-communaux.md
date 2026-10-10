# Plan de saisie éditoriale — première vague

## Objectif

Remplir progressivement les collections publiques vides du portail sans créer de contenu fictif et sans publier de donnée non validée.

Le CMS WordPress reste la source de vérité. Après chaque lot validé et publié, le portail Astro doit être reconstruit puis l’archive statique doit être régénérée.

## Ordre recommandé

### Vague 1 — priorité citoyenne

1. **Démarches administratives** (`ck_demarche`)
2. **Documents publics** (`ck_document`)
3. **Projets communaux** (`ck_projet`)

Ces trois collections sont déjà intégrées au portail, à la recherche globale et au workflow de relecture.

### Vague 2 — information institutionnelle

4. Sessions du conseil (`ck_session`)
5. Alertes (`ck_alerte`)
6. Événements institutionnels (`ck_evenement`)

### Vague 3 — transparence

7. Marchés publics (`ck_marche`)
8. Rapports, délibérations et pièces justificatives associées aux projets ou documents.

## Fiche minimale par type

### Démarche administrative

- intitulé officiel ;
- description courte ;
- public concerné ;
- pièces à fournir ;
- étapes ;
- délai ;
- coût ;
- lieu ou service compétent ;
- téléphone ou email validé ;
- source officielle ;
- date de vérification.

### Document public

- titre officiel ;
- catégorie ;
- année ou période ;
- description ;
- fichier vérifié et lisible ;
- date de publication ;
- service producteur ;
- source ou référence administrative.

### Projet communal

- nom officiel ;
- résumé ;
- description ;
- zone ou village concerné ;
- état d’avancement ;
- calendrier ;
- maître d’ouvrage ;
- budget si publiable ;
- documents justificatifs ;
- date de dernière mise à jour.

## Workflow obligatoire

```text
Collecte de la source officielle
        ↓
Saisie par le Gestionnaire communal
        ↓
Enregistrement en brouillon
        ↓
Envoi pour relecture : pending
        ↓
Contrôle par l’Éditeur communal
        ↓
Publication WordPress
        ↓
Reconstruction Astro
        ↓
Vérification du portail public
```

## Règles de validation

- Ne pas remplir les champs avec des suppositions.
- Ne pas recopier une information non attribuée à une source officielle.
- Ne pas publier de numéro de téléphone ou d’email non validé.
- Ne pas téléverser un document illisible, incomplet ou sans référence.
- Ne pas utiliser les contenus de recette comme contenus publics.
- Conserver les dates et intitulés officiels.
- Vérifier l’affichage mobile après chaque lot important.

## État public relevé le 10 octobre 2026

| Collection | Total public | Action |
|---|---:|---|
| Démarches | 0 | Première priorité |
| Documents | 0 | Première priorité |
| Projets | 0 | Première priorité |
| Sessions | 0 | Deuxième vague |
| Alertes | 0 | Deuxième vague |
| Événements | 0 | Deuxième vague |
| Marchés | 0 | Vague transparence |

Les collections déjà alimentées restent disponibles comme références institutionnelles : 56 élus, 37 agents, 21 services et 18 commissions.

## Critère de clôture d’un lot

Un lot est considéré comme terminé lorsque :

- les sources officielles sont identifiées ;
- les fiches sont saisies par le Gestionnaire ;
- toutes les fiches sont relues par l’Éditeur communal ;
- les contenus sont publiés dans WordPress ;
- le build Astro est relancé ;
- les routes publiques et la recherche affichent les contenus ;
- aucune fiche de test ne reste publiée.
