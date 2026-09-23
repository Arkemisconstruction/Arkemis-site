# Initialisation des pages WordPress

L’initialiseur se trouve dans **Outils → Initialisation Arkemis**. Il doit être lancé manuellement par un administrateur et ne s’exécute jamais à l’activation du plugin.

Il crée uniquement les Pages manquantes : Accueil, Terrassement et excavation, Béton architectural, Rénovation, À propos, Contact, Demander une soumission et Politique de confidentialité. La route `/realisations/` reste exclusivement l’archive du type de contenu `realisation`.

Les nouvelles Pages de services reçoivent le modèle `service`. Demander une soumission reçoit le modèle `contact` et une copie éditable de la composition `arkemis/contact`, qui contient le bloc `arkemis/quote-form`. Les compositions existantes des services, de la page À propos et de la politique sont copiées dans `post_content` lors de la création. Accueil, À propos et Politique de confidentialité utilisent les modèles automatiques fondés sur la hiérarchie WordPress.

Une Page existante, y compris un brouillon ou une Page placée dans la corbeille, n’est jamais recréée. Son titre, son statut et son contenu ne sont jamais modifiés. La case optionnelle « Affecter les templates Arkemis aux pages existantes correspondantes » autorise seulement la mise à jour de `_wp_page_template` pour les trois services et la demande de soumission.

Si aucune page d’accueil statique n’est configurée, Accueil est sélectionnée. Si aucune page de confidentialité WordPress n’est configurée, la politique Arkemis est sélectionnée. Toute configuration existante est conservée.

Le rapport distingue les pages créées, déjà existantes ou ignorées, les templates affectés ou ignorés et les erreurs. Il est conservé temporairement pour l’administrateur courant uniquement.
