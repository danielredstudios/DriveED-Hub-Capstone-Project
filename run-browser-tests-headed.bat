@echo off
title DriveED Hub - Automated Browser Testing (Headed Mode)
echo ===============================================================
echo   Running Automated Browser Tests (Interactive Visible Browser)
echo   Target: http://localhost:8004/drived-hub
echo ===============================================================
node scripts/automated-browser-test.js --headed --slowMo 200
pause
