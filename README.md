# Quiz System API built with Laravel
[![License: MIT](https://img.shields.io/badge/License-MIT-lime.svg)](https://opensource.org/licenses/MIT)
![example workflow](https://github.com/emtiazzahid/laravel-quiz-system/actions/workflows/laravel.yml/badge.svg)


## Demo: [API](https://squiz-api.herokuapp.com/) | [APP](https://squiz-app.netlify.app/)

[![Api Run in Postman](https://run.pstmn.io/button.svg)](https://app.getpostman.com/run-collection/1269242-ade4235a-9b13-42ad-bd2f-910cacd801ba?action=collection%2Ffork&collection-url=entityId%3D1269242-ade4235a-9b13-42ad-bd2f-910cacd801ba%26entityType%3Dcollection%26workspaceId%3Df794fb65-ef0e-4088-b879-87f113b728e1)


# Installation
1. Clone this repo
```
git clone https://github.com/emtiazzahid/laravel-quiz-system.git
```

2. Install composer packages
```
cd laravel-quiz-system
```
```
composer install
```

3. Create and setup .env file
```
cp .env.example .env
```
```
php artisan key:generate
```
```
php artisan jwt:secret
```

4. put database credentials in .env file

   for testing add database name on DB_TEST_DATABASE


6. Migrate and insert records
```
php artisan migrate --seed
```

6. To run test
```
.\vendor\bin\phpunit
```

#### Optionals:
#### [Laravel scout support](https://laravel.com/docs/9.x/scout)
to use laravel scout set your driver configs to .env

example for algolia
```
SCOUT_DRIVER=algolia
SCOUT_QUEUE=true
ALGOLIA_APP_ID=
ALGOLIA_SECRET=
```

### For Frontend Repo Visit: [quiz app](https://github.com/emtiazzahid/quiz-app)

## Screenshot
![QuizApp Demo](https://user-images.githubusercontent.com/10188029/133921722-532ff8b1-0abf-443a-af66-92a93655fc35.gif)

### Additional packages used
[jwt-auth](https://github.com/tymondesigns/jwt-auth)

## License

The MIT License (MIT)

