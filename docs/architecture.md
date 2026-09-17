# Architecture technique Arkemis

## Séparation des responsabilités
Arkemis est un Block Theme : style.css, theme.json version 3, templates HTML de blocs et parties HTML. Le thème ne définit ni CPT, ni champs, ni taxonomies, ni redirections, ni données SEO.
Arkemis Core conserve la logique métier et les données indépendantes de la présentation. Tout futur traitement métier, réglage de l’entreprise, intégration ou relation entre contenus appartient au plugin, jamais au thème.
Les données résident dans la base WordPress, et les médias dans la médiathèque ; changer de thème ne les supprime pas. Le plugin doit rester actif pour les exposer. Sa désactivation ne supprime rien.
Les fonctions SEO spécialisées seront confiées à une extension SEO indépendante du thème, avec les éventuelles intégrations propres à Arkemis dans arkemis-core.

## Services
Les services sont des Pages WordPress natives, sans CPT de service.
Les trois services principaux validés sont : Terrassement et excavation, Béton architectural et Rénovation. Aucune Page ni aucun terme n’est créé automatiquement.
Foyers est exclu de cette liste ; les anciennes URLs et informations associées restent à préserver pour la migration SEO.
La taxonomie interne `arkemis_service` classe uniquement les réalisations, avec zéro archive publique pour éviter de concurrencer les Pages de services.
Un éventuel lien terme → Page sera ajouté au plugin lorsque les Pages existeront ; aucun ID ni domaine n’est codé en dur.

## Contenus structurés
CPT : `realisation`, REST et éditeur de blocs activés.
- Titre : post_title.
- Description détaillée : post_content.
- Extrait : post_excerpt, utilisé pour les listes.
- Image principale : image mise en avant.
- Service(s) : taxonomie arkemis_service, source unique du classement.
- Ville : _arkemis_city.
- Secteur : _arkemis_sector ; pas d’adresse privée.
- Description courte structurée : _arkemis_summary, distincte de l’extrait éditorial.
- Année facultative : _arkemis_year.
- Galerie ordonnée : _arkemis_gallery, tableau d’identifiants de pièces jointes images.
- Avant et après : _arkemis_before et _arkemis_after, une paire d’identifiants images pour la base.
Les légendes et textes alternatifs restent dans la médiathèque. Les champs enregistrés sont destinés à un contenu public, sans données confidentielles.
Les métadonnées participent aux révisions. Les termes et les médias nécessitent également une sauvegarde de la base et des fichiers.
Le rendu des champs structurés et le sélecteur visuel de médias seront développés ultérieurement ; les templates affichent actuellement le contenu natif.

## Administration
admin.php prépare une boîte de saisie compatible avec l’éditeur de blocs et une colonne Ville.
Le classement par service utilise le panneau natif de taxonomie et sa colonne d’administration.
Sauvegarde : nonce, droits edit_post, exclusion des sauvegardes automatiques/révisions, nettoyage des valeurs et échappement à l’affichage.
La galerie accepte provisoirement les identifiants séparés par des virgules. Les identifiants qui ne sont pas des images sont ignorés.
La recette WordPress et l’amélioration de l’ergonomie des médias restent nécessaires.

## Compatibilité des templates
| Fichier | Rôle / sélection |
| --- | --- |
| index.html | Repli obligatoire d’un Block Theme ; boucle héritée |
| front-page.html | Accueil ; configuration prévue : Page statique |
| page.html | Toutes les Pages ordinaires : à propos, services, confidentialité |
| service.html | Modèle personnalisé déclaré dans theme.json pour les Pages |
| contact.html | Modèle personnalisé déclaré dans theme.json pour les Pages ; aucun formulaire encore intégré |
| single.html | Repli pour un article |
| single-realisation.html | Fiche du CPT dont la clé exacte est realisation |
| archive-realisation.html | Archive du CPT, route /realisations/ |
| search.html | Résultats de recherche, requête héritée et pagination |
| 404.html | Page introuvable |
| parts/header.html, parts/footer.html | Parties déclarées dans theme.json |

Les anciens noms proposés page-service.html et page-contact.html sont remplacés par service.html et contact.html : l’affectation ne dépend pas du slug.
Aucun PHP dans les templates HTML. Les modèles de pages et fiches incluent post-content ; les listes utilisent Query avec inherit=true.
Les modifications de templates dans l’éditeur de site sont stockées en base et peuvent remplacer les fichiers du thème : les exporter dans Git avant livraison.

## Composants futurs
Hero, cartes de services/réalisations, texte-image, galerie, étapes, FAQ, appels à soumission.
Les patterns seront développés dans le thème lors de l’étape visuelle, sans logique de stockage.
Aucun constructeur lourd ni chaîne de compilation n’est requis pour cette base.

## Références vérifiées
- https://developer.wordpress.org/themes/templates/templates/
- https://developer.wordpress.org/themes/templates/template-hierarchy/
- https://developer.wordpress.org/themes/global-settings-and-styles/
- https://developer.wordpress.org/reference/functions/register_post_meta/
