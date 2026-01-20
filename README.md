# Talent HUB — Plateforme de recrutement (MVC PHP From Scratch)

## 📌 Description du projet

Talent HUB est une plateforme de mise en relation entre **candidats**, **recruteurs** et **administrateurs**, développée en **PHP 8 orienté objet**, selon une **architecture MVC sans framework**.

Le projet a pour objectif principal de construire une **fondation d’authentification réutilisable**, puis de l’étendre vers une **application de gestion d’offres d’emploi complète**, en respectant de bonnes pratiques d’architecture, de sécurité et de maintenabilité.

---

## 🎓 Objectifs d’apprentissage

- Mettre en place une architecture **MVC claire et maintenable**
- Appliquer le **Repository Pattern** pour isoler l’accès aux données
- Utiliser **PDO avec requêtes préparées** pour la sécurité
- Implémenter une **authentification multi-rôles from scratch**
- Gérer correctement **sessions et cookies**
- Implémenter le **soft delete (archivage)** des données
- Utiliser **AJAX** pour des interactions dynamiques
- Implémenter un **upload sécurisé de fichiers** (CV, images)

---

## 🧑‍💼 Rôles utilisateurs

- **Administrateur**
- **Recruteur (Entreprise)**
- **Candidat**
- **Visiteur (non connecté)**

Chaque rôle possède des **droits spécifiques** et un **dashboard dédié**.

---

## ⚙️ Stack technique

- PHP 8 (POO)
- Architecture MVC (sans framework)
- PDO + requêtes préparées
- MySQL
- JavaScript 
- HTML / CSS
- Authentification et rôles from scratch

---

## 🧩 Fonctionnalités

### 🔐 Authentification & Sécurité

- Inscription / Connexion / Déconnexion
- Hashage des mots de passe
- Gestion de session sécurisée
- Redirection automatique selon rôle
- Protection des routes via middlewares
- Accès interdit clair (HTTP 403)

### 🛠 Back Office — Admin & Recruteur

#### Administrateur

- CRUD **Catégories**
- CRUD **Tags**
- Gestion & archivage des offres (soft delete)
- Tableau de bord avec statistiques :
  - Offres par catégorie
  - Tags populaires
  - Recruteurs actifs
- Restauration des offres archivées (optionnel)

#### Recruteur

- Inscription via formulaire entreprise
- Création / édition / suppression d’offres
- Association catégories + tags
- Consultation des candidatures
- Accès aux CV et profils candidats

### 👤 Front Office — Candidats & Visiteurs

- Consultation des offres disponibles
- Page détail d’une offre
- Recherche dynamique  :
  - mots-clés
  - catégories
  - tags *(optionnel)*
- Postulation avec upload sécurisé de CV
- Jobs recommandés basés sur :
  - compétences
  - prétentions salariales

---

## 📂 Architecture du projet (détaillée)

```
/public
 ├── .htaccess
 └── index.php

/sql
 ├── ddl.sql
 └── dml.sql

/src
 ├── .htaccess
 ├── View.php

 ├── Controllers
 │   ├── AdminController.php
 │   ├── ApplicationController.php
 │   ├── AuthController.php
 │   ├── CategoryController.php
 │   ├── JobController.php
 │   ├── RecruiterController.php
 │   └── TagController.php

 ├── Core
 │   ├── Database.php
 │   └── Router.php

 ├── enumTypes
 │   └── RoleName.php

 ├── Interfaces
 │   └── DashboardInterface.php

 ├── Middleware
 │   ├── AuthMiddleware.php
 │   └── RoleMiddleware.php

 ├── Models
 │   ├── Application.php
 │   ├── Category.php
 │   ├── Company.php
 │   ├── Job.php
 │   ├── Role.php
 │   ├── Tag.php
 │   └── User.php

 ├── Repositories
 │   ├── BaseRepository.php
 │   ├── CategoryRepository.php
 │   ├── CompanyRepository.php
 │   ├── JobRepository.php
 │   ├── RoleRepository.php
 │   ├── TagRepository.php
 │   └── UserRepository.php

 ├── Services
 │   └── AuthService.php

 └── Views
     ├── layout.twig
     ├── admin
     │   └── dashboard.twig
     ├── auth
     │   ├── login.twig
     │   └── register.twig
     ├── candidate
     │   └── dashboard.twig
     ├── errors
     │   ├── 403.twig
     │   └── 404.twig
     └── recruiter
         └── dashboard.twig
```

---

## 📑 Critères d’acceptation

- ✔ Auth fonctionnelle avec sessions et hash
- ✔ Redirection par rôle
- ✔ Protection des routes
- ✔ CRUD catégories, tags, offres
- ✔ Soft delete opérationnel
- ✔ Upload CV sécurisé
- ✔ MVC + Repositories + PDO
- ❌ Aucun package d’auth externe

---

## 🚀 Lancement du projet

1. Cloner le projet
2. Configurer la base de données dans `/config/database.php`
3. Importer le fichier SQL
4. Lancer le serveur :
```bash
php -S localhost:8000 -t public
```
5. Accéder à l’application via :
```
http://localhost:8000
```

---

## 🧪 Améliorations possibles

- Permissions fines (RBAC avancé)
- Pagination & cache
- Tests unitaires
- API REST
- Notifications email

---

## 👨‍💻 Auteur

Projet réalisé dans un cadre collectif pédagogique (MVC From Scratch — PHP 8).