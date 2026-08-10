# EuroSocietes — Project Guide for the AI Model

This document is the single source of truth for any AI model working on **EuroSocietes.com**.
Read it fully before touching the codebase. It derives from `data/cdc.pdf` (Cahier des charges),
`data/Phase.txt` (roadmap) and `data/DataScraping.txt` (content sourcing strategy).

---

## 1. What the project is

**EuroSocietes.com** is a next-generation **company-profile (fiche entreprise) directory**
whose goal is to become the **European SEO reference for company pages**.

Each profile is a single, extremely rich, monolithic page designed to:

- answer every search related to a **company**
- answer every search related to its **activity / trade**
- answer every search related to its **city, department, region, country**
- **convert visitors into customers** for the listed company
- allow **monetization** (subscriptions, ads, badges, premium blocks)
- be fully **responsive** (desktop / tablet / mobile)

**Cardinal rule:** all information lives on **one URL per company**.
No tab, no link, no AJAX redirect leads to another content page. Content is server-rendered
and fully indexable.

---

## 2. Global vocabulary (French domain terms — keep these in the UI)

| Term | Meaning |
|---|---|
| Fiche entreprise | The company profile page (the core deliverable) |
| Passeport de confiance | "Trust passport" — fixed orange block validating a company's identity |
| Valider vos coordonnées | "Validate your coordinates" — CTA for non-subscribed companies |
| Établissement | Establishment (a company can have several) |
| Dirigeant | Legal representative / manager |
| Code APE / NAF | French activity classification code |
| SIREN / SIRET / TVA | French company identifiers (TVA = intra-community VAT number) |
| Spécialités locales | Local specialties (food, crafts, traditions) |
| Repères historiques | Historical landmarks / timeline |
| Erreurs à éviter | "Mistakes to avoid" educational block |
| Quartier | City neighborhood |
| Département / Région | French administrative divisions |
| Métier | Trade / profession (plumber, restaurant, taxi, dentist…) |
| Fiche radiée | Company removed from the register (struck off) |
| Convention collective | Collective bargaining agreement |

---

## 3. Platform roles

Defined at Phase 1 (authentication). Roles:

1. **Admin** — full control of the back-office, monetization, users, blocks.
2. **Éditeur (Editor)** — edits / validates content.
3. **Entreprise (Company)** — manages its own profile, coordinates, subscription.
4. **Utilisateur (User)** — anonymous / registered visitor (favorites, compare).

---

## 4. Technology & architecture (Phase 1 — priority)

**Retained stack (2026):**

```
eurosocietes/
├── backend/        → Laravel 12 API (PHP 8.4, FPM Alpine)
├── frontend/       → Next.js (site / app)          [à venir]
└── cms/            → WordPress (Blog / CMS)        [à venir]

                Cloudflare
                    │
      ┌─────────────┴─────────────┐
      │                           │
 Next.js                      WordPress
site / app                    Blog / CMS
      │                           │
      └──────────┬────────────────┘
                 ▼
            Laravel API
                 │
      ┌──────────┼─────────────┐
      ▼          ▼             ▼
 PostgreSQL    Redis         OpenSearch
```

- **Monorepo** with `backend/` (Laravel API), `frontend/` (Next.js) and `cms/` (WordPress).
- **Dev environment = Docker Compose** (everything via containers on Windows):
  `docker compose up -d --build` — services: `app` (php:8.4-fpm-alpine + artisan serve),
  `composer` (profile tools), `postgres` (postgres:17-alpine), `redis` (redis:7-alpine).
- **Prod environment** kept separate, behind Cloudflare.
- **Configuration** via `.env` files (never commit secrets; `.env.example` is committed).
- **Logging system** and **centralized error handling** from day one.
- **Authentication** + the 4 roles above (Phase 1 done).

Useful commands (Windows host):

```
docker compose up -d --build            # start everything
docker compose ps                       # status
docker compose exec app php artisan ... # run artisan in the app container
docker compose run --rm composer <cmd>  # run composer (ex: composer install)
```

**Done for Phase 1:** the project starts cleanly → verified (`http://localhost:8000` → 200,
migrations on PostgreSQL OK, Redis cache OK).

Reference architecture notes:

- **Monolithic, server-rendered pages** (SEO requirement, no client-side rendering of content).
- Cache (server-side), Gzip/Brotli, lazy images, WebP/AVIF, deferred maps → **PageSpeed ≥ 90**.
- Slugs pattern: `fr.eurosocietes / région / département / ville / métier / entreprise`.

---

## 5. Database design (Phase 2 — the most important phase)

Full schema **before writing any page**. Tables (with SQL indexes planned from the start):

`entreprises` · `établissements` · `dirigeants` · `villes` · `départements` · `régions` ·
`pays` · `activités_naf` · `spécialités` · `quartiers` · `monuments` · `espaces_verts` ·
`faq` · `contenus_ia` · `passeports` · `documents` · `abonnements` · `utilisateurs` ·
`publicités` · `historique` · `imports` · `statistiques` · `recherches`

Design constraints:

- Handles **millions of company profiles** and **indexable URLs** from the start.
- Every table carries the indexes required by the most common queries (search, geo filters, slugs).
- Full import/update history; deletions of struck-off companies are **logged**, never silent.

---

## 6. Data import engine (Phase 3 — the heart of the platform)

Built **before displaying any profile page**:

- **INSEE import** (SIRENE base): companies, establishments, activity codes.
- **Updates** (new companies, changes).
- **Removal of struck-off (radiées) companies**.
- **Resume after interruption** (idempotent, batched).
- **Logs** and **quality control** at every step.
- Full **history** of every import.

This engine must be robust enough to ingest millions of records repeatedly.

---

## 7. Administration back-office (Phase 4 — before the public site)

The Admin must be able to do everything **without new development**:

- Edit any company profile.
- **Enable / disable / move / reorder any block** on any page.
- Edit page texts.
- Manage **advertisements**, **users**, **Passeports**, **AI-generated content**.
- Manage **subscriptions** and **badges**.
- Manage **monetization** elements (see §13).

The block system is the backbone: every block of the fiche is an independently
toggleable, orderable unit.

---

## 8. AI content generator (Phase 5 — before public pages)

A dedicated engine producing content blocks **independently of display**. Empty blocks
are simply not shown. Generated content types:

- Présentation entreprise · Histoire · Ville · Quartier · FAQ · Métiers ·
  Spécialités locales · Économie · Culture · Erreurs à éviter · « Le saviez-vous ? » ·
  Personnalités locales · Comprendre le secteur.

**Content rules:**

- **No copy-paste** — every text must be rewritten/reinterpreted.
- **No images or photos** are scraped.
- Texts combine multiple public sources (see §12) → rich, hard-to-reproduce editorial content.
- Each block is generated on its own (regenerating one block must not affect the others).
- AI: **open-source LLM or Batch OpenAI** (choice to be confirmed).

---

## 9. SEO generator (Phase 6)

- URLs and **slugs** per the pattern `région / département / ville / métier / entreprise`.
- **Meta** titles/descriptions per page.
- **Schema.org**: `LocalBusiness`, `Organization`, `BreadcrumbList`, `FAQPage`,
  `PostalAddress`, `GeoCoordinates`, `AggregateRating` (only if actually used).
- **Sitemaps** and Google Search Console submission.
- **Internal linking** (maillage interne) between related pages.

SEO constraints on pages:

- Single H1, H2 per section, H3 for subsections.
- No AJAX loading that prevents Google from reading content.
- **301 redirects** for merged/deduplicated profiles.
- Production chain: deduplicated data → AI processing → search engine integration →
  301 → sitemaps → GSC → indexing.

---

## 10. Public site — pages to build (Phases 7–10)

1. **Fiche entreprise** (Phase 7) — the giant single page (see §11).
2. **Territorial pages** (Phase 8): Ville → Département → Région → Pays.
3. **Métier pages** (Phase 9): Plombier, Restaurant, Taxi, Dentiste, etc.
4. **Métier × ville crossings** (Phase 10): *Plombier Lyon*, *Restaurant Nice*, *Avocat Paris*.
5. **Recherche** (Phase 12): search by entreprise, ville, métier, département, spécialité.

---

## 11. The « Fiche entreprise » page (Phase 7 — the core deliverable)

The full block order from the CDC. **Admin must be able to toggle/reorder every block.**

1. **Header** — logo, search, menu, connexion, favoris, comparer.
2. **Fil d'Ariane (breadcrumb)** — France › Région › Département › Ville › Entreprise.
3. **Bloc entreprise** — nom, état d'activité, logo, photo principale, activité, adresse,
   téléphone, site, email, SIREN, SIRET, TVA, date de création, effectif, forme juridique,
   dirigeant, capital, code APE, convention collective, horaires, réseaux sociaux, GPS.
   - **If not subscribed:** phone / site / email / contacts are **masked**,
     replaced by the **« Valider vos coordonnées »** CTA.
4. **Passeport de confiance** — fixed right-hand orange block: title, pictogram, status text,
   CTAs (« Valider vos coordonnées », « Rassurer vos futurs clients », « En savoir plus »).
5. **Publicité** — sponsored blocks (ex: « Création gratuite de société »).
6. **Barre d'icônes** — Plan, Autour, Activités, Repères historiques, Quartiers,
   Infos pratiques, Spécialités locales → each icon scrolls to its block.
7. **Carte interactive** — Leaflet + OpenStreetMap, markers, zoom, distance calculation.
   « Autour »: restaurants, hôtels, banques, parkings, transports, musées, espaces verts,
   administrations, services, monuments.
8. **Quartier** — présentation, histoire, population, commerces, immobilier, accès, statistiques.
9. **Espaces verts** — list with distance, walking / driving time, map.
10. **Spécialités locales** — bouchons lyonnais, pralines roses, quenelles, soieries, etc.
11. **Le saviez-vous ?** — anecdotes, culture, histoire, insolite, traditions, personnalités.
12. **Sites touristiques** — map, list, distance, description.
13. **Histoire** — présentation, chronologie, **timeline verticale**, dates, illustrations.
14. **Erreurs à éviter** — pedagogical block, 5–10 errors depending on the activity.
15. **Questions fréquentes (FAQ)** — **min. 30 questions**, answers 150–300 words, all on-page,
    SEO-optimized, **no link to another URL**.
16. **Personnalités locales** — photos, dates, biographies.
17. **Culture** — événements, festivals, musées, expositions, calendrier.
18. **Économie locale** — population, entreprises, revenus, immobilier, tourisme, créations,
    chômage, salaire moyen.
19. **Comprendre le secteur** — guide, fonctionnement, diplômes, marché, réglementation,
    assurances, conseils.
20. **Créer une société dans ce secteur** — list of neighboring cities.
21. **Services entreprises** — création gratuite, création Europe, holding, banque premium,
    dissolution, radiation, TUP.
22. **Autres activités à fort potentiel** — auto-generated: same city, same department,
    same activity.
23. **Footer** — mentions, CGU, réseaux sociaux, signaler une erreur.

**AI generation rule:** each block is generated independently; a block without content
is simply not rendered.

---

## 12. Content sourcing strategy (from DataScraping.txt)

Pages are built from a combination of structured data + AI rewriting of multiple **public**
sources (this differentiates EuroSocietes from classic directories).

Priority sources:

- **Gallica** (BnF) — old books, tourist guides, journals (public domain pre-1929). ⭐⭐⭐⭐⭐
- **Archives départementales & municipales** — local history, industries, censuses. ⭐⭐⭐⭐⭐
- **Base Mérimée / Base Palissy** (POP Culture) — monuments, classified objects. ⭐⭐⭐⭐⭐
- **OpenStreetMap** — monuments, quarters, buildings, parks, maps. ⭐⭐⭐⭐⭐
- **data.gouv.fr** — open government datasets. ⭐⭐⭐⭐⭐
- **INSEE** — population, industry, employment, housing statistics. ⭐⭐⭐⭐⭐
- **Persée** — academic articles (economic/local history, architecture). ⭐⭐⭐⭐⭐
- **Wikipedia** — starting point for themes and source discovery only (never copy text). ⭐⭐⭐⭐
- **Géoportail** — historical maps, old communes, cadastre. ⭐⭐⭐⭐
- **Europeana** — European digital library. ⭐⭐⭐⭐
- **Wikisource / Commons Wikimedia** — old full texts, free photos. ⭐⭐⭐⭐

**Hard rules:**
- No copy-paste; texts must be **reinterpreted**.
- **No images / photos** scraped.
- Facts should trace back to public-domain or openly licensed sources.

---

## 13. Monetization (Phase 11 + §29 of CDC) — all admin-configurable

- **Passeport de confiance** (subscription-based).
- **Validation des coordonnées** (unmasking contacts).
- **Publicités internes**.
- **Mise en avant** de l'entreprise (highlighting).
- **Blocs sponsorisés**, **bandeaux promotionnels**.
- **CTA personnalisés**, **formulaires de contact**.
- **Liens premium**.
- **Badges**: Entreprise vérifiée, Passeport actif, Coordonnées validées, etc.
- Subscriptions = payment + coordinate validation + passport + ads + badges.

---

## 14. How the model should work on this project

1. **Read this file** and the `data/*` references before making changes.
2. **Respect the phase order** (§4–§13): architecture → database → import → admin → AI →
   SEO → fiche → territories → métiers → crossings → subscriptions → search → optimization.
   Do not build UI before its foundation exists.
3. **Block system mindset:** content is modular. Every UI section is a block with
   enable/disable/move/reorder + independent generation.
4. **One URL per entity**, server-rendered, SEO-clean. No client-side-only content.
5. **Database-first:** think in terms of the tables and indexes from §5.
6. **Never commit secrets** (`.env`, API keys). Use the environment file pattern.
7. **Keep French UI/product terms** exactly as documented in §2.
8. Verify work with the project's lint / test / typecheck commands before finishing.

## 15. Definition of done (checklist)

- [ ] Change maps to a documented phase and respects its ordering.
- [ ] Database changes include indexes and follow the table naming (§5).
- [ ] Any new page/section is built as a toggleable, reorderable block.
- [ ] Content generation is independent per block; empty blocks are hidden.
- [ ] SEO constraints respected (single URL, semantic H1/H2/H3, Schema.org where relevant).
- [ ] Performance budget respected (cache, lazy images, no render-blocking content).
- [ ] No secrets committed; `.env`-based config.
- [ ] Lint + typecheck + tests pass.

---

*Source files: `data/cdc.pdf` (Cahier des charges), `data/Phase.txt`, `data/DataScraping.txt`,
`data/Fonctionnalite.jpg`, `data/FonctionnaliteCle.jpg`, `data/Maquette/*.jpg`.*
