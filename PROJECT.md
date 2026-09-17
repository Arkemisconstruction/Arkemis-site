# Projet Arkemis

Le nouveau site présentera Arkemis et ses trois services principaux :

- Terrassement et excavation
- Béton architectural
- Rénovation

Foyers ne fait plus partie des services principaux du nouveau site. Toute ancienne URL ou information liée à cette activité doit être conservée dans l’inventaire de migration SEO jusqu’à décision explicite sur son traitement.

## Règles du projet

- Site de développement autorisé : `mistyrose-whale-605490.hostingersite.com`.
- Site de production protégé : `arkemis.ca`.
- Ne jamais modifier, déployer, importer, supprimer ou écraser quoi que ce soit sur `arkemis.ca` sans l’autorisation écrite explicite du propriétaire du projet.
- Avant toute écriture distante, confirmer le domaine ciblé.
- Ne jamais enregistrer de mot de passe, jeton ou clé dans le projet.
- Construire un thème WordPress Arkemis personnalisé, léger, rapide et sans constructeur lourd comme Elementor.
- Travailler localement et conserver un historique Git avant les déploiements.
- Étape actuelle : préparer le thème et le plugin uniquement en local, sans déploiement ni modification sur Hostinger, et sans publication sur GitHub.

## Architecture validée

- Thème `arkemis` : Block Theme WordPress, présentation uniquement.
- Plugin `arkemis-core` : réalisations, taxonomie interne des services, champs structurés et administration ; données conservées lors d’un changement de thème.
- Les services restent des Pages WordPress natives, jamais un Custom Post Type.
- La liste des trois services ci-dessus est validée, sans création automatique de Pages ni de termes.
- Documentation technique : `docs/architecture.md`.
- Gestion SEO et migration des anciennes URLs : `docs/seo-migration.md` et `docs/redirects.csv`.
- Développement visuel complet et déploiements non commencés.
