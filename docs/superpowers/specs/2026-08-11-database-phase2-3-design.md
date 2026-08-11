# Design — Base de données EuroSocietes (Phase 2) + Moteur d'import (Phase 3)

**Date** : 2026-08-11
**Branche** : develop
**Statut** : approuvé

## Contexte

EuroSocietes est un annuaire français d'entreprises (type annuaire local + contenu éditorial généré). Objectif : gérer des millions de fiches entreprises et d'URL indexables dès le départ. Ce document couvre la conception complète de la base de données (Phase 2) et le moteur d'import (Phase 3), dimensionnés pour la scalabilité, l'efficacité, la robustesse, la recherche et l'indexation.

## Décisions actées

| Décision | Choix |
|----------|-------|
| SGBD | PostgreSQL 17 + PostGIS |
| Convention tables | Français partout (sauf infra framework : `jobs`, `cache`, `personal_access_tokens`, spatie) |
| Convention colonnes | Français `snake_case` |
| `users` → `utilisateurs` | Renommage via migration propre |
| Recherche | FTS PostgreSQL (`tsvector`) + index GIN + `pg_trgm` + `unaccent` |
| Géospatial | PostGIS `geography(Point/Polygon, 4326)` + index GIST |
| Périmètre import | SIRENE (unités légales + établissements) + COG (pays/régions/départements/communes) |
| Radiées | Soft-delete + entrée `historique` (action `radiation`) |
| Lat/lng | Géocodage du siège + fallback coordonnées de la commune (COG/Géofla) |
| Cadence | Full initial puis incrémental diff (fichiers INSEE créées/mises à jour/radiées) |
| Mode d'import | Asynchrone via queue Redis (lots = jobs, supervision Horizon) |
| FAQ / Contenus IA / Documents | Polymorphiques (`entity_type` / `entity_id`) |
| Abonnements / Publicités | Rattachés à l'entreprise (consommateur), utilisateur = payeur nullable |
| Rôles | spatie/laravel-permission (pas de colonne `role`) |
| Partitionnement | Non prévu au départ (index suffisants à 10M+ lignes) ; à réévaluer plus tard sur une clé pertinente |

## Fondations

Extensions (migration dédiée) : `postgis`, `pg_trgm`, `btree_gist`, `unaccent`.

Conventions : PK `BIGINT GENERATED ALWAYS AS IDENTITY` (pas d'UUID), timestamps Laravel sur toutes les tables, soft-deletes (`deleted_at`) sur les tables sensibles (entreprises, etablissements, utilisateurs).

## Référentiel géographique (COG)

### `pays`
- `id` PK, `code_iso2` UK, `code_iso3` UK, `code_insee` UK, `libelle`, `slug` UK, timestamps

### `regions`
- `id` PK, `code` UK (INSEE), `libelle`, `slug` UK, `pays_id` FK → pays, `geom geography(Polygon,4326)`, timestamps

### `departements`
- `id` PK, `code` UK (2–3 car.), `libelle`, `slug` UK, `region_id` FK → regions, `geom`, timestamps

### `villes`
- `id` PK, `code_insee` UK, `libelle`, `slug` UK, `departement_id` FK, `arrondissement`, `population`, `latlng geography(Point,4326)` GIST, timestamps

### `ville_code_postal` (pivot)
- `id` PK, `ville_id` FK, `code_postal`, `UNIQUE(ville_id, code_postal)`
- Une commune peut avoir plusieurs codes postaux (Paris, Lyon, Marseille).

## Référentiel activité

### `activites_naf`
- Hiérarchie complète NAF : `id` PK, `code` UK ("56.10A"), `section CHAR(1)` + `section_libelle`, `division` + `division_libelle`, `groupe` + `groupe_libelle`, `classe` + `classe_libelle`, `libelle`, timestamps

### `specialites`
- `id` PK, `libelle` UK, `slug` UK, `description`

### `entreprise_specialite` (pivot)
- `entreprise_id` FK, `specialite_id` FK, `PK(entreprise_id, specialite_id)`

## Cœur métier (SIRENE)

### `entreprises` (unité légale)
- `id` PK
- `siren VARCHAR(9)` UK, `CHECK (siren ~ '^[0-9]{9}$')`
- `denomination`, `nom`, `prenoms`, `sigle`, `enseigne`
- `categorie_juridique`, `categorie_entreprise`, `tranche_effectifs`, `annee_effectifs`, `caractere_employeur`
- `etat_administratif CHAR(1)` (`'A'` actif / `'C'` radié), `statut_diffusion`
- `date_creation`, `date_radiation`, `date_debut_activite`
- `activite_naf_id` FK, `ville_id` FK (siège), `latlng geography(Point,4326)` GIST (siège), `adresse_complete TEXT`
- `slug` UK, `search_vector tsvector` GIN (dénormalisé : denomination, nom, enseigne, activite, ville, adresse), `allow_public_contacts BOOL`
- `visible BOOL` (publié), `deleted_at` (soft-delete), timestamps

### `etablissements`
- `id` PK
- `siret VARCHAR(14)` UK, `CHECK (siret ~ '^[0-9]{14}$')`, `nic VARCHAR(5)`
- `entreprise_id` FK (ON DELETE CASCADE), `est_siege BOOL`
- `activite_naf_id` FK, `etat_administratif`, `statut_diffusion`, `date_creation`, `date_debut`
- `numero_voie`, `indice_repetition`, `type_voie`, `libelle_voie`, `complement_adresse`, `code_postal`, `ville_id` FK, `libelle_commune`, `adresse_complete TEXT`
- `latlng geography(Point,4326)` GIST, `slug`, `deleted_at`, timestamps

Index : `UNIQUE(siret)`, composite `(entreprise_id, est_siege)`, B-tree `code_postal`, GIST `latlng`.

### `dirigeants`
- `id` PK, `entreprise_id` FK, `qualite` (PDG, Gérant…), `nom`, `prenoms`, `date_debut_fonction`, `est_principal`
- Source future : INPI/RNE (hors périmètre Phase 3).

## Territoire enrichi

### `quartiers`
- `id` PK, `ville_id` FK, `libelle`, `slug` UK, `description`, `zone geography(Polygon,4326)` GIST, `latlng GIST`, timestamps

### `monuments`
- `id` PK, `ville_id` FK, `quartier_id` FK nullable, `libelle`, `slug`, `type`, `ref_merimee`, `description`, `adresse`, `latlng GIST`, timestamps

### `espaces_verts`
- `id` PK, `ville_id` FK, `quartier_id` FK nullable, `libelle`, `type` (parc/jardin/square), `latlng GIST`, timestamps

## Contenu éditorial & IA (polymorphique)

### `faq`
- `id` PK, `entity_type` + `entity_id` (entreprise, ville, quartier, specialite), `question`, `reponse`, `ordre`, `visible`, timestamps
- Index `(entity_type, entity_id)`

### `contenus_ia`
- `id` PK, `entity_type` + `entity_id`, `type_contenu` (presentation, histoire, ville, quartier, faq, metiers, specialites_locales, economie, culture, erreurs), `contenu`, `statut` (pending/generating/done/failed), `modele`, `prompt_version`, `generated_at`, timestamps
- `UNIQUE(entity_type, entity_id, type_contenu)`

### `passeports`
- `id` PK, `entreprise_id` FK UNIQUE, `statut` (non_soumis/en_cours/valide/refuse/expire), `score_confidence SMALLINT`, `badges jsonb` GIN, `is_validated BOOL`, `validated_at`, `validateur_id` FK nullable, `commentaire`, timestamps

### `documents`
- `id` PK, `entity_type` + `entity_id`, `type` (kbis, justificatif…), `titre`, `chemin`, `mime_type`, `taille`, `statut_validation`, timestamps
- Index `(entity_type, entity_id)`

## Monétisation

### `abonnements`
- `id` PK, `entreprise_id` FK (consommateur), `utilisateur_id` FK nullable (payeur), `plan` (gratuit/essentiel/premium), `statut` (actif/expire/annule/en_essai), `stripe_id` UK nullable, `date_debut`, `date_fin`, `renouvellement_auto BOOL`
- `CHECK (date_fin IS NULL OR date_debut <= date_fin)`

### `publicites`
- `id` PK, `entreprise_id` FK nullable (annonceur), `utilisateur_id` FK nullable, `titre`, `description`, `emplacement` (fiche_entreprise/page_ville/sidebar…), `url_cible`, `image_path`, `statut` (brouillon/publie/archive), `date_debut`, `date_fin`, `budget`, `impressions`, `clics`
- `CHECK` dates cohérentes, index `(emplacement, statut)`

## Audit, import & analytics

### `historique` (audit — Phase 4)
- `id` PK, `entity_type` + `entity_id`, `action` (create/update/delete/restore/bloc_move/import/radiation), `avant jsonb`, `apres jsonb`, `utilisateur_id` FK nullable, `import_id` FK nullable, `ip`
- Index `(entity_type, entity_id, created_at)`

### `imports`
- `id` PK, `type` (sirene_unites/sirene_etablissements/cog/diff_unites/diff_etablissements), `source`, `fichier`, `statut` (pending/processing/completed/failed/partial/cancelled), `lignes_total`, `lignes_traitees`, `lignes_inserees`, `lignes_maj`, `lignes_radiees`, `lignes_erreur`, `resume_state jsonb`, `started_at`, `completed_at`, timestamps

### `import_logs`
- `id` PK, `import_id` FK, `niveau` (info/warning/error), `message`, `siren`, `siret`, `ligne`, `context jsonb`, `created_at`
- Index `import_id`

### `statistiques`
- `id` PK, `type` (vue_entreprise/vue_ville/clic_telephone/impression_publicite…), `entity_type` + `entity_id`, `periode DATE`, `compteur BIGINT`
- `UNIQUE(type, entity_type, entity_id, periode)`

### `recherches`
- `id` PK, `terme`, `nb_resultats`, `utilisateur_id` FK nullable, `ip`, `created_at`
- Index `terme`, `created_at`

## utilisateurs

Migration de renommage `users` → `utilisateurs` :
- `id`, `email` UK, `password`, `remember_token`, timestamps, soft-delete
- Rôles gérés par spatie (suppression du champ `role` éventuel)
- Mise à jour de `config/auth.php` (table du provider users) et des tests existants

## Index récapitulatif

| Type | Sur |
|------|-----|
| UNIQUE | `siren`, `siret`, `code_insee`, `code_iso2`, `code` NAF/COG, tous `slug` |
| B-TREE composite | `(ville_id, activite_naf_id)`, `(entreprise_id, est_siege)`, `(entity_type, entity_id)`, `(etat_administratif, visible)` |
| GIST PostGIS | toutes les colonnes `geography` (latlng, zone) |
| GIN | `search_vector` (tsvector FR), `pg_trgm` (denomination, nom, libelle, slug), jsonb `badges` |
| B-TREE | `code_postal`, `type_contenu`, `statut`, `emplacement`, `terme`, `periode` |

## Moteur d'import (Phase 3)

### Commandes Artisan
- `php artisan import:cog` — référentiel géographique (pays, régions, départements, communes, codes postaux, coordonnées commune)
- `php artisan import:sirene --type=unites|etablissements` — full initial
- `php artisan import:sirene --diff --type=unites|etablissements` — incrémental
- `php artisan import:sirene --resume --type=unites|etablissements` — reprise après interruption

### Architecture interne (services découplés et testables)
- `Import\Downloader` : streaming HTTP chunké vers `storage/app/imports/` (le stock fait plusieurs Go, jamais en mémoire)
- `Import\CsvReader` : lecture `league/csv` par lots de ~2000 lignes
- `Import\ImportService` : upsert bulk `INSERT ... ON CONFLICT (siren|siret) DO UPDATE` en transaction par lot
- `Import\Geocoder` : géocodage fin du siège (job asynchrone) + fallback coordonnées de la commune
- `Import\QualityChecker` : validation Luhn SIREN/SIRET, champs obligatoires, existence code NAF et code commune → lignes rejetées dans `import_logs` + rapport CSV des lignes rejetées

### Radiées
`etat_administratif != 'A'` → soft-delete de l'entreprise/établissement + entrée `historique` (action `radiation`) + purge des `contenus_ia` orphelins.

### Reprise après interruption
Chaque lot enregistre son checkpoint (`resume_state` JSON dans `imports` : offset + dernier SIREN/SIRET). `--resume` repart où ça s'est arrêté. Un lot échoué → `failed_jobs` + `import_logs`, les lots suivants continuent.

### Asynchrone
Chaque lot est un job Redis (`ImportChunkJob`) dispatché par la commande. Horizon supervise en dev/prod. `Schedule` quotidien des fichiers INSEE diff (créées / mises à jour / radiées).

### Contrôle qualité
Validation SIREN/SIRET (Luhn), présence champs obligatoires, cohérence référentiel (NAF, commune). Rapport CSV des lignes rejetées par import. Compteurs d'import mis à jour en temps réel dans `imports`.

## Ce qui est explicitement écarté

- Partitionnement des tables au démarrage (réévalué plus tard, sur une clé pertinente — pas `created_at`)
- Table `entreprises_search` redondante (rôle couvert par FTS `tsvector` + GIN)
- Import des dirigeants via INPI/RNE (Phase ultérieure)
- Index spatial au format MySQL `SPATIAL INDEX` (remplacé par PostGIS `geography` + GIST)
- Colonnes de rôle (`role` ENUM) sur `utilisateurs` (spatie)

## Tests

- Migrations : existence tables/colonnes/index/relations (test unitaire)
- Import : parsing CSV échantillon, upsert idempotent (relancer = même résultat), radiée → soft-delete + historique, reprise après `resume_state`, rejet lignes invalides
- Géo : fallback commune quand pas de géocodage
- FTS : scope de recherche retourne les bons résultats ; index GIN utilisable
- Tests exécutés sur PostgreSQL 17 + PostGIS (pas de SQLite)
