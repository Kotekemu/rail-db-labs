
## Запуск
docker compose up -d

## Подключение в DBeaver
Host: localhost
Port: 5432
Database: railway_db
User: railuser
Password: railpass

## Запуск фронта
npm i
quasar dev

## Запуск бэка
php artisan serve --host=127.0.0.1 --port=8000
