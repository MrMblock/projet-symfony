# Projet Blog Symfony

Projet de fin de module PRS BD 26.3 

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- Symfony CLI
- Base de données (MySQL/MariaDB/PostgreSQL)

Postgre de préférence pcq j'ai utilisé postgre donc sinon va falloir regéner toutes les migrations

## Installation

1. Cloner le projet

<pre>
git clone https://github.com/MrMblock/projet-symfony.git
cd projet-symfony
</pre>

2. Installer les dépendances

<pre>
composer install
</pre>

3. Configurer l'environnement

Il faut configurer un .env
<pre>
nano .env ou notepad .env
# DATABASE_URL="mysql://user:password@127.0.0.1:3306/nom_de_la_bdd?serverVersion=8.0.32&charset=utf8mb4"
</pre>

4. Créer la base de données et les tables

<pre>
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
</pre>

5. Charger les données de test (Fixtures)

<pre>
php bin/console doctrine:fixtures:load
</pre>

6. Lancer le serveur de développement

<pre>
symfony serve
</pre>

## Fonctionnalités

- **Blog** : Liste des articles, lecture d'un article, pagination.
- **Recherche** : Recherche par mot-clé dans les titres, contenus et catégories.
- **Utilisateurs** : Inscription, connexion, déconnexion, profil public.
- **Commentaires** : Ajout de commentaires (pour les utilisateurs connectés), modération via l'admin.
- **Administration** : Dashboard pour gérer les articles, catégories et commentaires (rôle ROLE_ADMIN requis).
- **Mode Sombre** : Interface compatible avec le mode sombre du système ou via un toggle.
- **API** : API pour récupérer les articles et commentaires sécurisée grâce à JWT
- **Favoris**
- **Vues**
- **Notifications**
-  **Profils**
- **Connexion Google**
- **Jukebox**
- **D'autres que j'ai surement oublié le mieux est de jouer avec le projet**

## Commandes utiles

Vider le cache (à faire souvent) :

<pre>
    php bin/console cache:clear
</pre>

Lancer les tests
<pre>
    php bin/phpunit
</pre>

## Créer un compte admin

Rendez-vous dans votre SGBD et modifiez les rôles de l'utilisateur que vous voulez rendre admin en '["ROLE_ADMIN"]'# projet-symfony
