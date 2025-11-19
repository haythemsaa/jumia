@echo off
echo ========================================================
echo    ICHRI TUNISIA - INSTALLATION AUTOMATIQUE
echo    Demarrage en 1 clic - Production Ready
echo ========================================================
echo.

REM Verifier Docker
docker --version >nul 2>&1
if errorlevel 1 (
    echo [ERREUR] Docker n'est pas installe.
    echo Visitez: https://docs.docker.com/get-docker/
    pause
    exit /b 1
)

echo [OK] Docker detecte
echo.

REM Demarrer les conteneurs
echo [INFO] Demarrage des conteneurs Docker...
docker-compose up -d

REM Attendre MySQL
echo [INFO] Attente de MySQL...
timeout /t 10 /nobreak >nul

REM Installer dependances
echo [INFO] Installation des dependances Laravel...
docker-compose exec -T app composer install --no-interaction

REM Configuration
echo [INFO] Configuration de Laravel...
docker-compose exec -T app cp .env.example .env
docker-compose exec -T app php artisan key:generate
docker-compose exec -T app php artisan storage:link

REM Migrations
echo [INFO] Creation de la base de donnees...
docker-compose exec -T app php artisan migrate:fresh --seed

REM Optimisations
echo [INFO] Optimisation...
docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache

echo.
echo ========================================================
echo            INSTALLATION TERMINEE !
echo ========================================================
echo.
echo Acces aux applications:
echo.
echo    Frontend:     http://localhost:8080
echo    API Backend:  http://localhost:8000
echo    PhpMyAdmin:   http://localhost:8081
echo.
echo Comptes de test:
echo.
echo    ADMIN:    admin@ichri.tn / password
echo    CLIENT:   client@ichri.tn / password
echo    VENDEUR:  vendor@ichri.tn / password
echo.
echo Votre marketplace est prete!
echo.
pause
