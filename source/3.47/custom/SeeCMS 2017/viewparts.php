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

$see->addViewPart('htmlheader');
$see->addViewPart('pageheader');
$see->addViewPart('pagefooter');
$see->addViewPart('blogintro');

$see->addViewPart('breadcrumb');
$see->configureViewPart( 'breadcrumb', 'controller', 'SeeCMSHelper' );
$see->configureViewPart( 'breadcrumb', 'controllerMethod', 'breadcrumbHTML' );

$see->addViewPart('searchresults');
$see->configureViewPart( 'searchresults', 'controller', 'SeeCMSSearch' );
$see->configureViewPart( 'searchresults', 'controllerMethod', 'search' );

$see->addViewPart('primarynavigation');
$see->configureViewPart( 'primarynavigation', 'controller', 'SeeCMSPage' );
$see->configureViewPart( 'primarynavigation', 'controllerMethod', 'navigation' );
$see->configureViewPart( 'primarynavigation', 'controllerPassin', array( 'startAtParent' => 0, 'startAtLevel' => 0, 'levelsToGenerate' => 1, 'html' => 1) );

$see->addViewPart('secondarynavigation');
$see->configureViewPart( 'secondarynavigation', 'controller', 'SeeCMSPage' );
$see->configureViewPart( 'secondarynavigation', 'controllerMethod', 'navigation' );
$see->configureViewPart( 'secondarynavigation', 'controllerPassin', array( 'startAtParent' => 0, 'startAtLevel' => 1, 'levelsToGenerate' => 4, 'html' => 1) );

$see->addViewPart('responsivemenu');
$see->configureViewPart( 'responsivemenu', 'controller', 'SeeCMSPage' );
$see->configureViewPart( 'responsivemenu', 'controllerMethod', 'navigation' );
$see->configureViewPart( 'responsivemenu', 'controllerPassin', array( 'startAtParent' => 0, 'startAtLevel' => 0, 'levelsToGenerate' => 1, 'html' => 1) );

$see->addViewPart('contactform');
$see->configureViewPart('contactform', 'controller', 'SeeCMSAdminAuthentication');
$see->configureViewPart('contactform', 'controllerMethod', 'loadEmail' );
$see->configureViewPart('contactform', 'controllerPassin', 1 );

$see->addViewPart('banners');
$see->configureViewPart( 'banners', 'controller', 'SeeCMSContent' );
$see->configureViewPart( 'banners', 'controllerMethod', 'loadADFcontent' );
$see->configureViewPart( 'banners', 'controllerPassin', array( 'adfs' => 1 ) );

$see->addViewPart('innerbanner');
$see->configureViewPart( 'innerbanner', 'controller', 'SeeCMSContent' );
$see->configureViewPart( 'innerbanner', 'controllerMethod', 'loadADFcontent' );
$see->configureViewPart( 'innerbanner', 'controllerPassin', array( 'adfs' => 3 ) );

$see->addViewPart('footercontactinformation');
$see->configureViewPart( 'footercontactinformation', 'controller', 'SeeCMSContent' );
$see->configureViewPart( 'footercontactinformation', 'controllerMethod', 'loadADFcontent' );
$see->configureViewPart( 'footercontactinformation', 'controllerPassin', array( 'adfs' => 2, 'type' => 'page', 'objectid' => 1 ) );

$see->addViewPart('gallery');
$see->configureViewPart( 'gallery', 'controller', 'SeeCMSMedia' );
$see->configureViewPart( 'gallery', 'controllerMethod', 'loadMediaByFolder' );
$see->configureViewPart( 'gallery', 'controllerPassin', array( 'parentID' => 1, 'mode' => 'data' ) );

$see->addViewPart('newsfeedmain');
$see->configureViewPart('newsfeedmain', 'controller', 'SeeCMSPost');
$see->configureViewPart('newsfeedmain', 'controllerMethod', 'feed' );
$see->configureViewPart('newsfeedmain', 'controllerPassin', array( 'tags' => false, 'archives' => true, 'postType' => 1 ));

$see->addViewPart('eventsfeedmain');
$see->configureViewPart('eventsfeedmain', 'controller', 'SeeCMSPost');
$see->configureViewPart('eventsfeedmain', 'controllerMethod', 'feed' );
$see->configureViewPart('eventsfeedmain', 'controllerPassin', array( 'tags' => false, 'archives' => true, 'postType' => 2 ));