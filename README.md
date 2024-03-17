[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
# Diploma thesis - Petr Kruntorád

This repository contains a diploma thesis focused on remotely managed multimedia kiosks which are based on Raspberry Pi. Web administration is using template AdminLTE with Symfony framework used for backend.

Used technologies:
- PHP
- Python
- Symfony 6.3.12
- Raspberry Pi
- [Selenium](https://www.selenium.dev/)
- [Live Components](https://ux.symfony.com/live-component)
- [StimulusBundle](https://github.com/symfony/stimulus-bundle)

Minimum requirements:
- Python 3.9
- PHP 8.1
- Raspberry Pi with minimum of 1 GB RAM

Used Template:
- [AdminLTE](https://github.com/ColorlibHQ/AdminLTE)

## Project init
1. Installs dependencies
```
composer install
npm install
```

2. Build assets
```
npm run build
```

3. Generate app secret
```
php bin/console app:generate-app-secret
```

4. Create .env from .env.example and fill all necessary data surrounded by < > (for example <app_secret>)

5. Creates database connection (optional)
```
php bin/console d:d:c
```

6. Loads fixtures (optional) - This will create default user with email dev@test.com with password Abcdef0/
```
php bin/console doctrine:fixtures:load
```

7. Updates database schema (optional)
```
php bin/console d:s:u --force
```
Or
- Import multimedia_kiosk.sql via your database management tool and create .env file from .env.example

## License
[MIT](https://opensource.org/licenses/MIT)

## Author
- Petr Kruntorád
- [LinkedIn](https://www.linkedin.com/in/petr-kruntorad)
- [Website](https://petrkruntorad.cz/)
