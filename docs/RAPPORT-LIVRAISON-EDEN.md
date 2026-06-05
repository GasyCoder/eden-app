# Rapport de livraison — Site institutionnel de l'École Doctorale EDEN

**À l'attention de :** Monsieur/Madame le Directeur de l'École Doctorale Écosystèmes Naturels (EDEN), Université de Mahajanga
**Objet :** Livraison du nouveau site web institutionnel de l'EDEN
**Adresse du site :** https://eden.mahajanga-univ.mg
**Espace d'administration :** https://eden.mahajanga-univ.mg/me/login
**Date :** 5 juin 2026
**Statut :** ✅ Projet terminé et mis en ligne

---

## 1. Résumé

Le nouveau site web de l'École Doctorale EDEN est **achevé, testé et mis en production**. Il présente l'École, ses équipes d'accueil, ses formations, ses thématiques de recherche, ses projets, ses partenaires et ses coordonnées, dans une identité visuelle **sobre, académique et professionnelle**.

La particularité du site est qu'il est **entièrement modifiable en ligne par l'administration**, sans aucune connaissance technique : il suffit de se connecter, puis de cliquer sur les petits crayons (✎) qui apparaissent à côté de chaque contenu pour le modifier directement sur la page.

---

## 2. Ce qui a été livré

- **Site public complet et responsive** (ordinateur, tablette, mobile) :
  navigation, bannière d'accueil, présentation, mission/vision/objectifs, mot de la direction,
  équipes d'accueil doctoral, parcours doctoral, thématiques de recherche, chiffres clés,
  partenaires, projets de recherche, contact et pied de page.
- **Édition en ligne (« inline ») de tous les contenus** : textes, titres, paragraphes, chiffres,
  liens, images, logo, et cartes répétables (équipes, projets, partenaires, etc.).
- **Gestion des images** : téléversement et remplacement direct (logo, photo de la direction,
  visuels des projets, carte de contact…).
- **Historique des modifications** avec possibilité de **restaurer** une version précédente.
- **Deux niveaux d'accès** (rôles) : *Super administrateur* et *Éditeur*.
- **Espace profil** permettant à chaque compte de changer son email et son mot de passe.
- **Identité visuelle académique** : vert universitaire profond, fonds clairs naturels,
  accent doré discret, titres en typographie serif, texte lisible.

---

## 3. Accès à l'administration

L'administration n'est **pas** accessible par les adresses habituelles (`/login`, `/register`, etc.),
qui renvoient volontairement une erreur 404 pour des raisons de sécurité.

**L'unique adresse de connexion est :**

> https://eden.mahajanga-univ.mg/me/login

### Comptes fournis

| Rôle | Identifiant (email) | Mot de passe |
|------|---------------------|--------------|
| Super administrateur | *communiqué séparément* | *communiqué séparément — à changer à la 1ʳᵉ connexion* |
| Éditeur | *communiqué séparément* | *communiqué séparément — à changer à la 1ʳᵉ connexion* |

> 🔒 **Important :** pour des raisons de sécurité, les mots de passe ne figurent pas dans ce document.
> Ils sont transmis par un canal séparé. **Merci de les modifier dès la première connexion**
> via l'espace *Profil*.

### Différence entre les deux rôles

| Action | Éditeur | Super administrateur |
|--------|:------:|:--------------------:|
| Modifier les contenus du site (textes, images, cartes…) | ✅ | ✅ |
| Ajouter / supprimer / réordonner les cartes | ✅ | ✅ |
| Consulter et restaurer l'historique | ✅ | ✅ |
| Modifier son propre profil (email, mot de passe) | ✅ | ✅ |
| Modifier les **mentions légales du pied de page** (copyright et crédit) | ❌ | ✅ |

L'éditeur peut tout gérer **sauf** les mentions légales en bas de page, réservées au super administrateur.

---

## 4. Guide d'utilisation rapide

### Se connecter
1. Aller sur **https://eden.mahajanga-univ.mg/me/login**
2. Saisir l'email et le mot de passe, puis **Se connecter**.
3. Une barre « Mode édition » apparaît en bas de l'écran : vous êtes en mode administration.

### Modifier un texte
1. Survoler le texte à modifier : un petit crayon **✎** apparaît à côté.
2. Cliquer dessus, corriger le texte, puis **Enregistrer** (ou *Annuler*).
   La modification est immédiatement visible sur le site public.

### Modifier une image ou le logo
1. Cliquer sur le crayon **✎** situé sur l'image (ou sur le logo en haut à gauche).
2. Choisir un fichier image (JPG, PNG, WebP). L'image est remplacée automatiquement.

### Gérer les cartes répétables (projets, partenaires, équipes, étapes…)
- **Ajouter** : bouton « Ajouter… » au-dessus de la liste.
- **Modifier** : crayon **✎** sur la carte.
- **Supprimer** : bouton *Supprimer* lors de la modification.
- **Réordonner** : flèches **↑ / ↓** pour monter ou descendre un élément.

### Consulter / restaurer l'historique
- Bouton **Historique** dans la barre d'édition : liste des modifications récentes,
  avec un bouton **Restaurer** pour revenir à une version antérieure.

### Réduire la barre d'édition
- Bouton **⤓** : réduit la barre en une petite pastille (utile pour voir le site sans gêne).
  La pastille **✎** en bas à gauche permet de la rouvrir. La préférence est mémorisée.

### Modifier son email / mot de passe
- Bouton **Profil** dans la barre d'édition → page *Profil administrateur*.

### Se déconnecter
- Bouton **Déconnexion** (en rouge) dans la barre d'édition.

---

## 5. Sécurité

- Seules les personnes connectées avec un compte autorisé peuvent modifier le site ;
  les visiteurs voient uniquement le site, sans aucun bouton d'édition.
- Adresse de connexion personnalisée (`/me/login`) ; les chemins classiques sont désactivés (404).
- Téléversements d'images contrôlés (type et taille).
- Contenus nettoyés avant enregistrement (protection contre les scripts malveillants).
- Mentions légales du pied de page protégées (super administrateur uniquement).

---

## 6. Aspects techniques (pour information / maintenance)

- **Technologies :** Laravel, Inertia.js, Vue 3, Vite, MySQL.
- **Hébergement :** serveur de l'Université (cPanel), nom de domaine `eden.mahajanga-univ.mg`.
- **Contenus :** stockés en base de données (modifiables en ligne, sauvegardables).
- **Qualité :** suite de tests automatisés (19 tests, tous au vert) couvrant l'authentification,
  les permissions par rôle, l'édition, l'historique et le profil.

---

## 7. Recommandations

1. **Changer les mots de passe** des deux comptes dès la première connexion.
2. Mettre à jour les contenus encore génériques : nom du Directeur, noms des responsables d'équipe,
   numéro de téléphone, et téléverser les photos réelles (direction, projets) et le logo officiel.
3. Prévoir une **sauvegarde régulière** de la base de données (côté hébergeur).
4. Communiquer l'adresse `/me/login` uniquement aux personnes habilitées.

---

## 8. Conclusion

Le site institutionnel de l'École Doctorale EDEN est **livré, fonctionnel et en ligne**.
Il offre à l'École un outil de communication moderne, crédible et surtout **autonome** :
l'équipe peut faire évoluer l'ensemble des contenus elle-même, en toute simplicité et en toute sécurité.

Nous restons à disposition pour toute formation à la prise en main ou tout accompagnement complémentaire.

*Document établi le 5 juin 2026.*
