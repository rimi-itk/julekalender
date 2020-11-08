# Calendar

```sh
composer install
bin/console doctrine:migrations:migrate --no-interaction
yarn build
```

## Admin user

```sh
bin/console user:create admin@example.com password ROLE_ADMIN
```
