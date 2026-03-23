# Changelog

## [1.4.1] - 2026-03-17

### Changed
- Bouton « Télécharger tous les certificats » dans le block (sidebar) : migration de l'ancien système synchrone (`download.php`) vers le système de téléchargement asynchrone (tâche adhoc + modal de progression + banner persistante)
- Le seuil de 30 certificats pour le mode asynchrone est supprimé pour le bouton « Download All » — le téléchargement passe systématiquement en mode async
- Le module AMD `async_download` est chargé dans le block en plus de la page de gestion, avec les chaînes de langue nécessaires
- La banner de progression asynchrone fonctionne maintenant hors de la page de gestion (fallback sur `#page-content` ou `body`)

## [1.4.0] - 2026-03-09

### Changed
- Page de gestion : le tableau des certificats est maintenant chargé dynamiquement via AJAX avec pagination côté serveur — la page ne charge plus les 12 000+ certificats en mémoire PHP
- Page de gestion : les 4 sections de filtrage (dates, cours, cohorte, utilisateur) sont regroupées en onglets Bootstrap au lieu de cartes empilées verticalement
- `controller.php` : la méthode `get_certificates_data()` ne charge plus les enregistrements individuels, utilise `count_all_certificates()` pour le total
- `styles.css` : réécriture complète avec styles pour les onglets, le tableau dynamique, les badges de type, la pagination, et les animations

### Added
- Nouveau module AMD `certificate_table.js` : tableau dynamique avec pagination, tri par colonne, recherche avec debounce, badges de type de certificat, et initialisation de l'autocomplete Moodle
- Nouvel endpoint AJAX `ajax/search_certificates.php` pour la recherche/pagination côté serveur
- Nouvelles méthodes dans `certificate_query.php` : `search_all_certificates()`, `count_search_results()`, `build_union_subqueries()`, `apply_search_filter()` — utilisant `UNION ALL` pour combiner les 5 types de certificats en une requête paginée unique
- Barre de recherche avec debounce (300ms) filtrant par nom, email, cours, modèle et code
- Autocomplete Moodle (`core/form-autocomplete`) sur les 3 select de filtrage (cours, cohorte, utilisateur)
- Badges colorés par type de certificat dans le tableau (Tool Certificate = bleu, Custom Cert = vert, etc.)
- Nouvelles chaînes de langue EN/FR pour le tableau, la recherche et les types

## [1.3.0] - 2026-03-09

### Changed
- Téléchargement asynchrone : traitement par lots de 500 certificats au lieu d'un traitement monolithique — la tâche adhoc se re-planifie automatiquement après chaque lot, éliminant les problèmes de mémoire et de timeout sur les grands volumes (10 000+ certificats)
- Comptage des certificats : remplacement du chargement complet des données par des requêtes `COUNT(*)` SQL via 5 nouvelles méthodes `count_*_issues()` dans `certificate_query.php`
- Gestion mémoire dans `add_certificates_to_zip()` : ajout de `unset($filecontent)` après chaque certificat et `gc_collect_cycles()` toutes les 50 itérations

### Added
- Nouvelle méthode `generate_zip_batch()` dans le packager pour le traitement paginé avec ajout incrémental au ZIP
- Nouveau champ `batchoffset` dans la table `block_download_cert_tasks` pour sauvegarder la progression entre les lots
- Appel à `raise_memory_limit(MEMORY_EXTRA)` au début de la tâche adhoc

## [1.2.2] - 2026-02-20

### Fixed
- Téléchargement asynchrone : les formulaires « par cours », « par cohorte » et « par utilisateur » passaient toujours en synchrone car le `data-cert-count` était à 0 en dur — le compteur est maintenant mis à jour dynamiquement selon l'option sélectionnée
- Modale de progression : ajout d'un bouton de fermeture et d'un message informatif indiquant que l'utilisateur peut quitter la page et revenir plus tard
- Banner « archive prête » : affiche maintenant le type d'archive et le nombre de certificats pour identifier l'archive

### Added
- Fichier de traduction français (`lang/fr/block_download_certificates.php`) avec toutes les chaînes traduites
- Nouvelles chaînes de langues pour la modale (`async_can_close`) et le banner par type (`async_zip_ready_label_*`)

## [1.2.1] - 2026-02-20

### Added
- Script CLI `purge_all_certificates.php` pour supprimer tous les certificats de la plateforme (5 types : tool_certificate, customcert, mod_certificate, simplecertificate, certificatebeautiful) avec confirmation interactive et nettoyage des fichiers associés

### Fixed
- Script `generate_test_certificates.php` : les certificats générés étaient des PDF vides (0 octets) à cause d'un cache de pages périmé dans l'objet template — rechargement du template depuis la DB avant l'émission
- Script `generate_test_certificates.php` : ajout d'éléments visibles au template (bordure, titre, nom étudiant, date, code de vérification) et correction des champs de formulaire (`userfield`, `display`, `dateitem`)
- Téléchargement ZIP : les certificats avec le même nom de fichier (même user + même template) étaient ignorés silencieusement au lieu d'être renommés avec un suffixe (`_2`, `_3`…) — tous les 5 types de certificats sont concernés
- Nom de fichier des certificats `tool_certificate` : ajout du nom du cours pour différencier les certificats d'un même utilisateur

## [1.2.0] - 2026-02-20

### Added
- Système de téléchargement asynchrone pour les lots ≥ 30 certificats
  - Nouvelle table `block_download_cert_tasks` pour le suivi des tâches
  - Adhoc task `generate_zip` pour la création ZIP en arrière-plan
  - Scheduled task `cleanup_expired` (toutes les heures) pour nettoyer les ZIP expirés (>24h), les tâches échouées (>7j) et les tâches bloquées (>2h)
  - 3 endpoints AJAX : `create_task.php`, `task_status.php`, `download_zip.php`
  - Module AMD `async_download.js` avec modal Bootstrap, barre de progression temps réel et auto-download
- Méthodes `generate_zip_to_file()`, `fetch_certificates_for_type()`, `count_certificates_for_type()` dans le packager
- Callback de progression dans `add_certificates_to_zip()` pour le suivi en temps réel
- Méthode `get_pending_tasks()` dans le controller pour restaurer les tâches en cours au chargement de page

## [1.1.0] - 2026-02-20

### Changed
- Refactoring majeur du `controller.php` (3331 → 361 lignes) en façade légère avec extraction de 3 classes :
  - `certificate_retriever.php` : récupération du contenu PDF (stockage fichier + HTTP)
  - `certificate_query.php` : centralisation de toutes les requêtes SQL
  - `certificate_packager.php` : création ZIP, génération de noms de fichiers, envoi
- Factorisation de la logique ZIP dupliquée via `add_certificates_to_zip()` et `create_and_send_zip()`
- API publique du controller conservée à 100% — aucun fichier appelant modifié

## [1.0.1] - 2026-02-20

### Fixed
- Correction des sections vides (par cours, par cohorte, par utilisateur) sur la page index : ajout des vérifications `table_exists('tool_certificate_issues')` manquantes dans `get_courses_with_certificates()` et `get_users_with_certificates()`
- Ajout de `try/catch` autour de chaque requête SQL dans `get_courses_with_certificates()` et `get_users_with_certificates()` pour isoler les erreurs par type de certificat
- Correction de l'erreur fatale `Call to undefined method get_simplecertificate_via_authenticated_api()` lors du téléchargement groupé : la méthode n'existait pas, remplacée par un fallback vers la méthode URL
- Correction des erreurs `Duplicate value found in column 'id'` dans `get_simplecertificate_issues()` : le `LEFT JOIN` sur `c.shortname = si.coursename` pouvait produire des doublons si plusieurs cours partagent le même shortname, remplacé par une sous-requête scalaire
