# Quiz System API built with Laravel


API: https://squiz-api.herokuapp.com/

App : https://squiz-app.netlify.app/


# Installation
1. Clone this repo
```
git clone https://github.com/emtiazzahid/laravel-quiz-system.git
```

2. Install composer packages
```
cd laravel-quiz-system
composer install
```

3. Create and setup .env file
```
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

    put database credentials in .env file

4. Migrate and insert records
```
php artisan migrate --seed
```

5. To run test
```
.\vendor\bin\phpunit
```
