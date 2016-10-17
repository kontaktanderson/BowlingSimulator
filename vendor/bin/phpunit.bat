@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../phpunit/phpunit/phpunit
C:\xampp\php\php "%BIN_TARGET%" %*
