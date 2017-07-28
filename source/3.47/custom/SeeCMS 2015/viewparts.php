<?php

/*********************************************/
/**************** VIEW PARTS *****************/
/*********************************************/

$see->addViewPart('content1', true);
$see->configureViewPart('content1','contentViewPart', true);
$see->addViewPart('content2', true);
$see->configureViewPart('content2','contentViewPart', true);
$see->addViewPart('content3', true);
$see->configureViewPart('content3','contentViewPart', true);
$see->addViewPart('content4', true);
$see->configureViewPart('content4','contentViewPart', true);
$see->addViewPart('content10', true);
$see->configureViewPart('content10','contentViewPart', true);
$see->configureViewPart('content10','useDisplayViewPart', 'eventbuttons');
$see->addViewPart('content11', true);
$see->configureViewPart('content11','contentViewPart', true);
$see->configureViewPart('content11','useDisplayViewPart', 'eventimage');

$see->addViewPart('htmlheader');
$see->addViewPart('pageheader');
$see->addViewPart('pagefooter');
$see->addViewPart('map');

$see->addViewPart('primarynavigation');
$see->configureViewPart( 'primarynavigation', 'controller', 'SeeCMSPage' );
$see->configureViewPart( 'primarynavigation', 'controllerMethod', 'navigation' );
$see->configureViewPart( 'primarynavigation', 'controllerPassin', array( 'startAtParent' => 0, 'startAtLevel' => 0, 'levelsToGenerate' => 1, 'html' => 1) );

$see->addViewPart('secondarynavigation');
$see->configureViewPart( 'secondarynavigation', 'controller', 'SeeCMSPage' );
$see->configureViewPart( 'secondarynavigation', 'controllerMethod', 'navigation' );
$see->configureViewPart( 'secondarynavigation', 'controllerPassin', array( 'startAtParent' => 0, 'startAtLevel' => 1, 'levelsToGenerate' => 1, 'html' => 1) );

$see->addViewPart('banners');
$see->configureViewPart( 'banners', 'controller', 'SeeCMSContent' );
$see->configureViewPart( 'banners', 'controllerMethod', 'loadADFcontent' );
$see->configureViewPart( 'banners', 'controllerPassin', array( 'adfs' => 1) );

$see->addViewPart('newsfeed');
$see->configureViewPart('newsfeed', 'controller', 'SeeCMSPost');
$see->configureViewPart('newsfeed', 'controllerMethod', 'feed' );
$see->configureViewPart('newsfeed', 'controllerPassin', array( 'tags' => true, 'archives' => true, 'limit' => 2 ));

$see->addViewPart('newsarchive');
$see->configureViewPart('newsarchive', 'controller', 'SeeCMSPost');
$see->configureViewPart('newsarchive', 'controllerMethod', 'archiveList' );

$see->addViewPart('loginform');
$see->configureViewPart('loginform', 'controller', 'SeeCMSWebsiteUser');
$see->configureViewPart('loginform', 'controllerMethod', 'loginForm');
$see->configureViewPart('loginform', 'controllerPassin', array( 'loggedInMessage' => 'You are logged in.', 'redirect' => '../members', 'onlyRedirectOnLogin' => false ) );

$see->addViewPart('newsfeedmain');
$see->configureViewPart('newsfeedmain', 'controller', 'SeeCMSPost');
$see->configureViewPart('newsfeedmain', 'controllerMethod', 'feed' );
$see->configureViewPart('newsfeedmain', 'controllerPassin', array( 'tags' => true, 'archives' => true ));

$see->addViewPart('eventsfeed');
$see->configureViewPart('eventsfeed', 'controller', 'SeeCMSPost');
$see->configureViewPart('eventsfeed', 'controllerMethod', 'feed' );
$see->configureViewPart('eventsfeed', 'controllerPassin', array( 'tags' => true, 'archives' => true, 'postType' => 2 ));

$see->addViewPart('eventsarchive');
$see->configureViewPart('eventsarchive', 'controller', 'SeeCMSPost');
$see->configureViewPart('eventsarchive', 'controllerMethod', 'archiveList' );
$see->configureViewPart('eventsarchive', 'controllerPassin', array( 'postType' => 2 ));

$see->addViewPart('gallery');
$see->configureViewPart( 'gallery', 'controller', 'SeeCMSMedia' );
$see->configureViewPart( 'gallery', 'controllerMethod', 'loadMediaByFolder' );
$see->configureViewPart( 'gallery', 'controllerPassin', array( 'parentID' => 41, 'mode' => 'data' ) );

$see->addViewPart('contactform');
$see->configureViewPart('contactform', 'controller', 'SeeCMSAdminAuthentication');
$see->configureViewPart('contactform', 'controllerMethod', 'loadEmail' );
$see->configureViewPart('contactform', 'controllerPassin', 1 );