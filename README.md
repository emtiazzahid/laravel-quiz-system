# Quiz System API built with Laravel
![Heroku](https://scrutinizer-ci.com/g/emtiazzahid/laravel-quiz-system/badges/quality-score.png?b=master&s=0348db35ecefcd904d7b79418e1f627e452bd13e)
![Heroku](https://scrutinizer-ci.com/g/emtiazzahid/laravel-quiz-system/badges/build.png?b=master&s=e4952fa027452bb103e589c47d8b3edeb5b3c2bf)

API: https://squiz-api.herokuapp.com/

[![Run in Postman](https://run.pstmn.io/button.svg)](https://app.getpostman.com/run-collection/1269242-ade4235a-9b13-42ad-bd2f-910cacd801ba?action=collection%2Ffork&collection-url=entityId%3D1269242-ade4235a-9b13-42ad-bd2f-910cacd801ba%26entityType%3Dcollection%26workspaceId%3Df794fb65-ef0e-4088-b879-87f113b728e1)

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
