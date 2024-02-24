# Rumah Drone Test Skill

## Tools
- php 8.3.0 (required to be precise, because of the used of pusher package and if you still getting an error, you can refer to this [link](https://stackoverflow.com/questions/29822686/curl-error-60-ssl-certificate-unable-to-get-local-issuer-certificate/34883260#34883260))
- Laravel 10 (Laravel Jetstream)
- Pusher
- node 21.6.2
- Vue 3
- Vite 5
- Tailwindcss 3

## Installation
1. Clone this repository
2. Install the required packages
```bash
composer install
```
3. Create a new database
4. Copy the .env.example file and rename it to .env
5. Set the database connection in the .env file
6. Run the migration
```bash
php artisan migrate:fresh --seed
```
7. Install npm packages
```bash
npm install
```
8. Run the npm
```bash
npm run dev # or you can use npm run build
```
9. Run the server
```bash
php artisan serve
```
10. Open the browser and go to the localhost:8000
