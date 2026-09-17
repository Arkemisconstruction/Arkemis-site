# SEO et remplacement du site

## État actuel
Aucun site distant consulté ou modifié pour cette étape. Aucune ancienne URL supposée, aucune redirection activée.
Foyers ne figure plus parmi les trois services principaux du nouveau site. Conserver toute ancienne URL, contenu ou métadonnée de cette activité dans l’inventaire de migration. Son retrait de l’offre principale ne justifie aucune suppression ni redirection automatique ; décider de chaque URL après inventaire et validation.
Le thème ne produit pas de balises SEO manuelles.
WordPress fournit le titre du document, les canonical des vues singulières et son sitemap natif. Cela ne constitue pas encore une configuration SEO complète.

## Responsabilité prévue
Une seule extension SEO, à sélectionner et tester localement, sera responsable des titres personnalisés, meta descriptions, canonical complets, directives robots et sitemap final.
Ses données seront stockées en base indépendamment du thème ; les intégrations métier seront dans arkemis-core.
Ne pas ajouter un second générateur dans le thème ou le plugin. L’extension retenue devra remplacer proprement les sorties natives correspondantes.
Avant son choix, inventorier la solution SEO existante et préserver/importer ses métadonnées, plutôt que repartir de zéro.

## Titres et descriptions
Prévoir pour les Pages et réalisations : titre SEO, description éditoriale et aperçu.
Valeurs de repli : titre du contenu et nom de l’entreprise ; description courte/extrait uniquement si pertinent.
Aucun bourrage de mots-clés, aucune ville ou prestation inventée.
Les archives doivent avoir des titres propres ; les pages de recherche internes restent hors index.

## Canonical et sitemap
Une seule canonical absolue par page indexable, sur le domaine effectif de l’environnement.
Ne pas coder arkemis.ca dans le thème ni faire pointer automatiquement les canonical du développement vers la production.
Vérifier les variantes HTTPS, hôte, slash final, paramètres et pages paginées sans tout rabattre sur la première page.
Le sitemap doit contenir uniquement les URLs publiques, indexables et canoniques : Pages et réalisations publiées.
Exclure les brouillons, recherches et classement interne arkemis_service.
Le sitemap natif /wp-sitemap.xml reste le mécanisme de base tant qu’une extension ne le remplace pas ; vérifier le chemin final après choix.

## Environnement temporaire
Avant une future mise en ligne de développement autorisée : prévoir protection d’accès et noindex.
Ne pas considérer robots.txt comme une protection d’accès. Aucune option distante n’est modifiée maintenant.
Au lancement autorisé, retirer les protections de l’environnement final seulement après validation du domaine et des canonical.

## Plan de redirection : priorité de migration
1. Inventorier les anciennes URLs avec des sources autorisées : export WordPress, sitemap, crawl, Search Console et journaux disponibles.
2. Conserver les slugs existants quand ils conviennent ; noter chaque changement dans redirects.csv.
3. Associer chaque ancienne URL à la nouvelle page équivalente. Éviter la redirection générale vers l’accueil.
4. Indiquer explicitement 301 pour un remplacement permanent ; 410 uniquement pour un contenu supprimé sans équivalent et validé.
5. Vérifier conflits avec les URLs conservées, boucles, chaînes, encodages, paramètres et variantes de slash.
6. Choisir un seul moteur persistant : configuration serveur ou extension de redirection indépendante du thème. Ne pas importer le CSV automatiquement.
7. Tester dans l’environnement local : statut HTTP, en-tête Location, une seule étape et destination finale 200, sans redirection externe imprévue.
8. Avant bascule : sauvegarde fichiers/base/configuration et export des règles existantes ; préparer un retour arrière.
9. Après bascule autorisée : vérifier les anciennes URLs prioritaires, canonical, sitemap, liens internes et erreurs 404.

redirects.csv est un document de préparation, pas un format d’import promis à une extension.
Colonnes : ancienne URL, nouvelle URL (vide si 410), code HTTP, motif, statut de validation.
Aucune règle réelle ne sera ajoutée sans inventaire ; la publication sur arkemis.ca requiert l’autorisation écrite explicite du propriétaire.

## Références
- https://developer.wordpress.org/reference/functions/rel_canonical/
- https://developer.wordpress.org/reference/functions/wp_sitemaps_get_server/
