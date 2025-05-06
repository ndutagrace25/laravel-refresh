## The breakdown App backend

##### The Breakdown App is an app where creators are able to engage with their fan base in unique ways


### Pre-requisites

Prior to starting laravel project, the either one of the following has to be installed on local machine
  - **PHP** (`brew install php`)
  - **Composer** (`brew install composer`)
  - **XAMPP** [https://www.apachefriends.org/download.html] 
  - **MAMP** [https://www.mamp.info/en/downloads/]
  - 
  
### Project Setup
1. Clone this repo
2. rename **.env.example** to **.env** and change config inside for the database section as follows (as needed - it is possible to change port, database name, just make sure you have it on your database server)
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=breakdown
DB_USERNAME=breakdown
DB_PASSWORD=breakdown
```
3. run the following
```
composer install
php artisan migrate
php artisan serve
```
4. Go to postman and test endpoints
# laravel-refresh
# laravel-refresh
# laravel-refresh
# laravel-refresh
# laravel-refresh
