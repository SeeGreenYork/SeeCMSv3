<?php

/*********************************************/
/***************** SETTINGS ******************/
/*********************************************/

$see->version = 0.3;
$see->AESKey = '[AESKEY]';
$see->siteID = '[SITEID]';
$see->siteTitle = '[SITETITLE]';

ini_set( "error_log", "../php-error.log" );

$see->theme = '[THEME]'; // Theme

$see->setRootURL( '[ROOTURL]' );
$see->publicFolder = '[PUBLICFOLDER]';

$seecmsConfig['cmsRoot'] = '[CMSROOT]'; // CMS address 
$seecmsConfig['databaseSessions'] = false; // Store sessions in DB
$seecmsConfig['databaseSessionsMaxLifetime'] = 0; // If sessions are stored in DB override default PHP session maxlifetime (seconds), 0 = default (optional)

$seecmsConfig['DBHost'] = '[DBHOST]'; // MySQL host
$seecmsConfig['DBName'] = '[DBNAME]'; // MySQL DB
$seecmsConfig['DBUsername'] = '[DBUSERNAME]'; // MySQL username
$seecmsConfig['DBPassword'] = '[DBPASSWORD]'; // MySQL password

$seecmsConfig['supportMessage'] = '[CMSSUPPORTMESSAGE]';
$seecmsConfig['disableCORS'] = true;

$seecmsConfig['defaultDocumentStatus'] = 0;

$see->loadPlugin( 'SeeCMS', $seecmsConfig ); // Load SeeCMS