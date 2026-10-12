@echo off
title Mini Project Toko - Pemrograman Web Pertemuan 3
echo ========================================================
echo   Menjalankan Server PHP Built-in
echo   Mini Project CRUD Aman & Responsif
echo ========================================================
echo.
echo Akses aplikasi di browser Anda melalui:
echo http://localhost:8000
echo.
echo Tekan CTRL+C untuk menghentikan server.
echo ========================================================
echo.

if exist "C:\xampp\php\php.exe" (
    start http://localhost:8000
    "C:\xampp\php\php.exe" -S localhost:8000
) else (
    start http://localhost:8000
    php -S localhost:8000
)
pause
