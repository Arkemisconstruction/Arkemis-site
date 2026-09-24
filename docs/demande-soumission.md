# Demande de soumission

La Page WordPress native **Demander une soumission** utilise le slug `/demander-une-soumission/` et le modèle `contact`. Les appels à soumission du thème pointent vers cette adresse ; `/contact/` reste une Page distincte pour les coordonnées générales. Le contenu de la Page de soumission est une copie éditable de la composition `arkemis/contact` contenant le bloc `arkemis/quote-form`.

## Responsabilités

- Thème : composition, modèle, styles, labels, erreurs associées aux champs et confirmation. Le bloc dynamique `arkemis/quote-form` est éditable comme composant de la Page. Son script frontend place seulement le focus sur le message après traitement.
- `arkemis-core/inc/quote-requests.php` : validation, protection anti-spam, traitement POST et appel serveur-à-serveur vers l’endpoint public de la plateforme. WordPress ne recrée aucune logique CRM.

Les champs prénom, nom, type de projet, budget approximatif, description, échéancier et méthode de contact sont obligatoires. La description accepte de 10 à 5 000 caractères. La ville est facultative. La méthode Courriel exige une adresse valide ; Téléphone et Texto exigent un numéro avec indicatif régional (10 à 15 chiffres). Toute coordonnée fournie est validée même lorsqu’elle n’est pas la méthode préférée.

Les erreurs sont rendues côté serveur à proximité des champs, avec `aria-invalid`, `aria-describedby` et un résumé contenant des liens. Les valeurs nettoyées sont conservées après erreur. Le formulaire fonctionne sans JavaScript ; un petit script améliore seulement le déplacement du focus. Les champs ont des labels natifs et des attributs d’autocomplétion.

## Traitement et protections

Le POST est traité seulement sur le modèle `contact`. Nonce WordPress, jeton de formulaire signé valable deux heures, honeypot et limitation à dix tentatives sur une fenêtre glissante de quinze minutes par adresse réseau. La clé de limitation est un HMAC salé ; l’adresse brute n’est pas enregistrée. Les proxys ne sont pas déduits de headers fournis par le visiteur.

Une réservation par identifiant protège contre les envois simultanés ; un reçu sans coordonnées empêche la répétition du même envoi pendant 24 heures. Le POST réussi redirige en 303 vers une confirmation à identifiant aléatoire. Une URL de confirmation fabriquée ne suffit pas à afficher un succès.

Le plugin envoie un JSON à `POST https://plateforme.arkemis.ca/api/public/website-inquiries` contenant uniquement `first_name`, `last_name`, `phone`, `email`, `city`, `project_type`, `message` et `source`. `description` devient `message`; les libellés validés du budget, de l’échéancier et de la méthode de contact sont ajoutés lisiblement à ce message. `source` vaut toujours `WEBSITE`. Le formulaire ne recueille pas d’adresse, donc le champ API facultatif `address` est omis. Aucun champ inconnu, identifiant d’organisation ou identifiant tenant n’est envoyé.

Le `request_id`, issu du jeton signé propre à la soumission, est transmis uniquement dans `X-Arkemis-Idempotency-Key`. Le jeton API est lu côté serveur depuis la constante `ARKEMIS_PLATFORM_API_TOKEN`, à définir dans `wp-config.php`, ou depuis la variable d’environnement du même nom, puis transmis dans `X-Arkemis-Website-Token`. Il n’existe aucune valeur de repli et le jeton ne doit jamais être enregistré dans Git. L’appel utilise HTTPS vérifié, interdit les redirections et expire après dix secondes.

Les coordonnées ne sont pas stockées dans une table, un CPT, un transient ou les journaux par ce module. Elles sont transmises à la plateforme. Les transients contiennent uniquement des compteurs ou reçus techniques ; une réservation interrompue expire fonctionnellement après cinq minutes et peut être reprise avec le même jeton. Aucun fichier n’est accepté.

Après une réponse HTTP 2xx, l’action `arkemis_quote_received` fournit encore l’enveloppe nettoyée pour compatibilité locale. Un timeout, une erreur réseau ou une réponse hors 2xx conserve la saisie. Les erreurs 4xx indiquent que le service n’a pas accepté la demande; les erreurs 5xx indiquent une indisponibilité temporaire. Les journaux techniques contiennent seulement l’événement, le `request_id`, le statut HTTP ou un code d’erreur de transport ; jamais le jeton, le corps API ni les coordonnées.

## Configuration et validation sur serveur réel

En production, ajouter dans le `wp-config.php` non suivi, avant la ligne qui arrête l’édition : `define( 'ARKEMIS_PLATFORM_API_TOKEN', getenv( 'ARKEMIS_PLATFORM_API_TOKEN' ) );`, puis fournir `ARKEMIS_PLATFORM_API_TOKEN` dans la configuration d’environnement Hostinger. Une constante contenant directement le secret est aussi supportée, mais elle ne doit pas être ajoutée au dépôt.

À valider ultérieurement, avec autorisation sur l’environnement de développement :

- Configurer le jeton sur l’environnement de développement autorisé, soumettre une demande unique depuis `/demander-une-soumission/`, puis vérifier le message de confirmation et la demande correspondante dans la plateforme avec le même `request_id` dans les journaux techniques.
- Exclure `/demander-une-soumission/`, ses réponses POST et confirmations de tout cache serveur/CDN afin de préserver les nonces, erreurs et données saisies. Le module émet déjà des en-têtes sans cache.
- Vérifier HTTPS et la limitation des tentatives derrière le proxy réel. Ne faire confiance à un header d’adresse client qu’après configuration explicite d’un proxy de confiance.
- Définir, lors de la future phase CRM, la conservation, les accès et le traitement des demandes en échec. Cette version repose sur le courriel et n’ajoute pas de stockage de secours de données personnelles.

## Photos — phase suivante

Le champ d’ajout de photos n’est pas activé. Prévoir avant son activation : nombre et taille limités, vérification réelle des types et du contenu, renommage, stockage privé, contrôle des accès, durée de conservation et suppression. Éviter les pièces jointes arbitraires et les médias publics pour les photos envoyées par les visiteurs. Cette amélioration peut être ajoutée au contrat de demande sans modifier les contenus actuels.

## Recette initiale (avant ajout du budget)

Rendus à 1440 et 390 px inspectés : labels natifs, champs d’au moins 54 px de hauteur, focus clavier visible et aucun débordement horizontal. Validation serveur des champs obligatoires et invalides, coordonnées conditionnelles, conservation des valeurs et associations accessibles des erreurs vérifiées. Nonce invalide, jeton altéré, honeypot et limite de tentatives empêchent l’envoi.

Cette recette historique utilisait un transport courriel simulé. Le transport actuel est exclusivement l’appel serveur-à-serveur documenté ci-dessus.

## Mise à jour des choix et du destinataire

Le formulaire propose quatre types : terrassement / béton architectural, rénovation intérieure, rénovation extérieure et autre. Le budget approximatif est obligatoire et validé par liste fermée côté serveur. Son libellé est intégré dans `message`, jamais envoyé comme champ API distinct. Les sept tranches comprennent « À déterminer ».

Les demandes sont transmises à la plateforme Arkemis. L’adresse générale visible dans « Nous joindre » reste `info@arkemis.ca`. Les protections et l’architecture existantes sont conservées.

Recette de cette mise à jour : 28 combinaisons type/budget acceptées ; choix manquants ou inconnus refusés ; description contrôlée à 9, 10, 5 000 et 5 001 caractères. Protections nonce, honeypot, limitation des tentatives et doubles envois vérifiées. Rendus 1440/390, labels, focus, blocs et absence de débordement validés.
