# POC PHP + MySQL dockerisé

## Lancement

```bash
docker compose up --build
```

## Accès

- Application : http://localhost:8080
- MySQL : localhost:3307

## Route

- /
- /user.php

## Comptes

- MySQL root : `rootpass`
- MySQL applicatif : `appuser` / `apppass`

## Structure

- `app/` : code PHP
- `db/init/` : scripts SQL d'initialisation
- `Dockerfile` : image PHP 8.2 Apache avec extensions MySQL
- `docker-compose.yml` : orchestration web + base

## Mettre à jour

- Le code PHP uniquement : il suffit de réactualiser le navigateur
- La base de données :
```bash
docker compose down -v
docker compose up --build
```
- La structure Docker :
```bash
docker compose down
docker compose up --build
```
- Reset complet :
```bash
docker compose down -v --remove-orphans
docker compose up --build
```

## Accès au bash du conteneur

```bash
docker exec -it poc_php_web bash
```

# Les mauvaises configurations

## Dockerfile

Les mauvaises pratiques dans le Dockerfile

### Image PHP Cli

Cette image en elle-même n'est pas vulnérable, mais elle permet de contrôler précisémment comment nous souhaitons lancer
le processus Apache PHP. Ne pas s'appuyer sur des images standardisés sur les aspects sécurité présente un risque.

### Compte root utilisé

Le processus est lancé avec le compte root, ce qui en cas de RCE, donne un accès total sur le conteneur à l'attaquant.

### Absence de a2enmod rewrite

En l'absence de ce module, nous ne pouvons pas ajouter de fichiers tels que htaccess pour notamment sécuriser les répertoires d'upload.

## docker-compose.yml

Les mauvaises pratiques dans le docker-compose.yml

### Exposition de la base de données

La base de données est actuellement exposée sur le port 3307 de la machine hôte via un PAT. Il n'est pas nécessaire de réaliser cette translation, Docker gère tout seul via son sous-réseau les dépendances via le mot clef depends_on. La surface d'attaque est ici augmentée sans raison.

### Compte SQL root

Bien qu'un compte applicatif soit crée côté SQL, le serveur web utilise dans ses variables d'environnement le compte root, donnant accès à toutes les bases de données et à toutes les fonctions MySQL tel que LOAD_FILE, ce qui présente un risque majeur en cas de SQLi.

### secure-file-priv=""

Cette partie indique au moteur de base de données le répertoire dans lequel la fonction LOAD_FILE a le droit de lire des fichiers. En laissant les guillemets vides, la fonction peut lire dans n'importe quel dossier.

# Les vulnérabilités dans le code

## SQLi

Le code permet de réaliser des SQLi sur la recherche de ressources.

## XSS

Le code présente une faille XSS sur la recherche de ressources.

## File upload

Aucun contrôle réalisé sur les fichiers uploadés pour les avatars.

## LFI

Il est possible de réaliser une LFI via le système de langue en place.

# Travaux pratique

## Dump SQL

Réaliser un dump SQL de la tables users

## Dump complet via SQLMap (optionnel)

Montrer que via le compte root on peut accéder aux autre bases de données

## Variables d'environnement et /etc/passwd (LOAD_FILE)

Récupérer ces éléments et expliquer pourquoi nous ne pouvons pas récupérer le code source

## Changer le compte SQL

Changer le compte SQL dans le docker-compose.yml pour ne plus utiliser root, et réessayer la fonction LOAD_FILE.

## Diminuer la surface d'attaque

Ne plus exposer sur la machine hôte le service MySQL

## Vol de cookie

Récupérer un cookie sur webhook.site via la vulnérabilité XSS

## Sécuriser le cookie

Réécrire dans les en-tête de réponse le flag httpOnly via le reverse-proxy Nginx, puis réessayer le vol de cookie.

## Upload de backdoor

Uploader une backdoor à la place d'un avatar, et réaliser une RCE.
Récupérer le contenu du fichier user.php, puis les variables d'environnement.

## Crack de MDP

Casser les mots de passe du précédent dump de la table users.

## .htaccess

Mettre le module a2enmod rewrite en place dans le Dockerfile et ajouter un .htaccess dans le répertoire avatar pour blocker l'exécution de code PHP dans ce répertoire.
Retester la backdoor.

## Bloquer .php dans les uploads

Réécrire une règle côté Nginx pour bloquer les uploads de fichiers PHP.

## Trouver les logs Nginx via LFI

Récupérer les logs Nginx via LFI

## RCE, via LFI

Réaliser une RCE via LFI et afficher le contenu de /dev

## Accéder au système de fichier de la machine hôte via la RCE

Créer un répertoire et monter le disque de la machine hôte sur ce répertoire

## Supprimer Privileged = true et changer d'image Docker

Faire whoami via la RCE, réessayer de monter le disque de la machine hôte sur un répertoire.

## Activer le mod security côté Nginx

Vérifier si les différentes attaques sont bloquées.