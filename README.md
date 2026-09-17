# Site Arkemis

Base technique locale, version 0.1.0. Aucun déploiement ni installation WordPress effectués.

## Répartition
- `wp-content/themes/arkemis` : Block Theme, présentation exclusivement.
- `wp-content/plugins/arkemis-core` : réalisations, classement, champs, administration.
- `docs/architecture.md` : modèles et données.
- `docs/seo-migration.md` : SEO et remplacement du site.
- `docs/redirects.csv` : inventaire des redirections à compléter, sans exécution automatique.
- `docs/verification.md` : contrôles locaux et recette WordPress restante.

## Environnement cible
WordPress 6.6 minimum, PHP 8.1 minimum pour cette base ; utiliser une version maintenue lors de la mise en place de l’environnement. Le cœur WordPress, les médias et la base de données ne sont pas inclus.

## Mise en route future, dans un WordPress local
1. Monter/copier les deux dossiers dans le wp-content de l’instance locale.
2. Activer Arkemis Core, puis le thème Arkemis.
3. Créer les Pages validées ; définir une Page statique comme accueil dans Réglages > Lecture.
4. Affecter les modèles Service Arkemis et Contact Arkemis aux Pages correspondantes.
5. Créer les termes de classement des réalisations correspondant aux trois services validés : Terrassement et excavation, Béton architectural et Rénovation.
6. Ajouter une réalisation fictive locale pour la recette, puis la supprimer avant toute migration.

L’activation du plugin enregistre les routes, mais ne crée aucune Page, aucun terme ni contenu automatiquement.
Réserver `/realisations/` à l’archive du CPT : ne pas créer une Page concurrente à cette adresse.

## État
Ossature fonctionnelle à tester sous WordPress, sans design complet ni formulaire.
La fondation est conservée dans l’historique Git local. Chaque évolution doit être versionnée avant tout déploiement.
Aucun secret ne doit être enregistré. Aucun automatisme de publication n’est fourni.
