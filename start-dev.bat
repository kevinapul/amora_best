@echo off
echo Starting Laravel + Vite...

REM === Jalankan Laravel LAN ===
start "" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"

REM === Jalankan Vite LAN ===
start "" cmd /k "npm run dev"

exit
