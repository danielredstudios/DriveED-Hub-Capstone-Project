@echo off
title DriveED Hub - Automated Browser Testing (Headless)
echo ===============================================================
echo   Running Automated Browser Tests (Headless Mode)
echo   Target: http://localhost:8004/drived-hub
echo ===============================================================
node scripts/automated-browser-test.js
pause
