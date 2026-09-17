# Vérification et recette

## Contrôles de cette étape
Vérifier le JSON, les chemins des modèles déclarés, les références aux parties et l’équilibrage des délimiteurs de blocs.
Vérifier les différences Git et l’absence de règles de redirection actives.
Ces contrôles statiques ne valident pas le rendu ou l’exécution PHP.

## Limite de l’environnement
PHP n’est pas disponible en ligne de commande dans ce workspace ; aucune installation automatique effectuée.
Aucune instance WordPress locale n’a encore été fournie. Lint PHP et recette WordPress restent à exécuter avant livraison.

## Recette à exécuter dans une instance locale
- Lint de tous les fichiers PHP avec php -l.
- Activer plugin et thème avec WP_DEBUG ; vérifier l’absence d’erreurs.
- Vérifier les modèles dans l’éditeur, sans erreur de validation des blocs.
- Affecter le modèle service à deux Pages de slugs différents ; vérifier leur contenu.
- Créer une réalisation, ses termes, une galerie et une paire avant/après.
- Vérifier sauvegarde, rechargement, effacement volontaire, ordre de la galerie et restauration d’une révision des métadonnées.
- Vérifier qu’une sauvegarde sans nonce ou sans droits ne modifie pas les champs ; tester les écritures REST autorisées et refusées.
- Vérifier rejet des identifiants de médias non images.
- Vérifier archive, pagination, recherche, fiche et 404.
- Changer de thème : données, termes et champs d’administration toujours présents.
- Désactiver/réactiver arkemis-core : aucune donnée supprimée.
- Vérifier titres, canonical et sitemap réels après configuration SEO.
- Tester les règles de redirection validées avant toute bascule.
