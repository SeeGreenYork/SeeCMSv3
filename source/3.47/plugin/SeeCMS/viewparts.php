<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
 
/*********************************************/
/**************** VIEW PARTS *****************/
/*********************************************/

/* General */
$this->addViewPart( 'seecmsheader' );
$this->configureViewPart( 'seecmsheader', 'path', 'plugin/SeeCMS' );

$this->addViewPart( 'seecmssubheader' );
$this->configureViewPart( 'seecmssubheader', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmssubheader', 'controller', 'SeeCMSSite' );
$this->configureViewPart( 'seecmssubheader', 'controllerMethod', 'loadAll' );

$this->addViewPart( 'seecmsnavigation' );
$this->configureViewPart( 'seecmsnavigation', 'path', 'plugin/SeeCMS' );

$this->addViewPart( 'seecmsfooter' );
$this->configureViewPart( 'seecmsfooter', 'path', 'plugin/SeeCMS' );

$this->addViewPart( 'seecmsfooternosearch' );
$this->configureViewPart( 'seecmsfooternosearch', 'path', 'plugin/SeeCMS' );

$this->addViewPart( 'seecmsloadingscreen' );
$this->configureViewPart( 'seecmsloadingscreen', 'path', 'plugin/SeeCMS' );

$this->addViewPart( 'seecmsupdatealert' );
$this->configureViewPart( 'seecmsupdatealert', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmsupdatealert', 'controller', 'SeeCMSUpdate' );
$this->configureViewPart( 'seecmsupdatealert', 'controllerMethod', 'update' );
$this->configureViewPart( 'seecmsupdatealert', 'globalCache', true );
$this->configureViewPart( 'seecmsupdatealert', 'caching', true );
$this->configureViewPart( 'seecmsupdatealert', 'cachingTimeout', 43200 );

/* Pages */
$this->addViewPart( 'seecmspages' );
$this->configureViewPart( 'seecmspages', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmspages', 'controller', 'SeeCMSPage' );
$this->configureViewPart( 'seecmspages', 'controllerMethod', 'loadForCMS' );

$this->addViewPart( 'seecmseditpage', false, true );
$this->configureViewPart( 'seecmseditpage', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmseditpage', 'controller', 'SeeCMSPage' );
$this->configureViewPart( 'seecmseditpage', 'controllerMethod', 'loadForEdit' );

$this->addViewPart( 'seecmscreatepage' );
$this->configureViewPart( 'seecmscreatepage', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmscreatepage', 'controller', 'SeeCMSPage' );
$this->configureViewPart( 'seecmscreatepage', 'controllerMethod', 'create' );

$this->addViewPart( 'seecmsmovepage' );
$this->configureViewPart( 'seecmsmovepage', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmsmovepage', 'controller', 'SeeCMSPage' );
$this->configureViewPart( 'seecmsmovepage', 'controllerMethod', 'move' );

/* Posts */
$this->addViewPart( 'seecmsposts' );
$this->configureViewPart( 'seecmsposts', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmsposts', 'controller', 'SeeCMSPost' );
$this->configureViewPart( 'seecmsposts', 'controllerMethod', 'loadForCMS' );

$this->addViewPart( 'seecmseditpost' );
$this->configureViewPart( 'seecmseditpost', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmseditpost', 'controller', 'SeeCMSPost' );
$this->configureViewPart( 'seecmseditpost', 'controllerMethod', 'loadForEdit' );

/* Media */
$this->addViewPart( 'seecmsmedia' );
$this->configureViewPart( 'seecmsmedia', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmsmedia', 'controller', 'SeeCMSMedia' );
$this->configureViewPart( 'seecmsmedia', 'controllerMethod', 'loadForCMS' );

$this->addViewPart( 'seecmseditmedia' );
$this->configureViewPart( 'seecmseditmedia', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmseditmedia', 'controller', 'SeeCMSMedia' );
$this->configureViewPart( 'seecmseditmedia', 'controllerMethod', 'loadForEdit' );

/* Downloads */
$this->addViewPart( 'seecmsdownloads' );
$this->configureViewPart( 'seecmsdownloads', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmsdownloads', 'controller', 'SeeCMSDownload' );
$this->configureViewPart( 'seecmsdownloads', 'controllerMethod', 'loadForCMS' );

$this->addViewPart( 'seecmseditdownload' );
$this->configureViewPart( 'seecmseditdownload', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmseditdownload', 'controller', 'SeeCMSDownload' );
$this->configureViewPart( 'seecmseditdownload', 'controllerMethod', 'loadForEdit' );

/* Admin */
$this->addViewPart( 'seecmsadminusers' );
$this->configureViewPart( 'seecmsadminusers', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmsadminusers', 'controller', 'SeeCMSAdminAuthentication' );
$this->configureViewPart( 'seecmsadminusers', 'controllerMethod', 'loadAll' );

$this->addViewPart( 'seecmseditadminuser' );
$this->configureViewPart( 'seecmseditadminuser', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmseditadminuser', 'controller', 'SeeCMSAdminAuthentication' );
$this->configureViewPart( 'seecmseditadminuser', 'controllerMethod', 'loadForEdit' );

$this->addViewPart( 'seecmsadmingroups' );
$this->configureViewPart( 'seecmsadmingroups', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmsadmingroups', 'controller', 'SeeCMSAdminAuthentication' );
$this->configureViewPart( 'seecmsadmingroups', 'controllerMethod', 'loadAllGroups' );

$this->addViewPart( 'seecmseditadmingroup' );
$this->configureViewPart( 'seecmseditadmingroup', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmseditadmingroup', 'controller', 'SeeCMSAdminAuthentication' );
$this->configureViewPart( 'seecmseditadmingroup', 'controllerMethod', 'loadGroupForEdit' );

$this->addViewPart( 'seecmscmsupdate' );
$this->configureViewPart( 'seecmscmsupdate', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmscmsupdate', 'controller', 'SeeCMSUpdate' );
$this->configureViewPart( 'seecmscmsupdate', 'controllerMethod', 'update' );

/* Analytics */
$this->addViewPart( 'seecmsanalytics' );
$this->configureViewPart( 'seecmsanalytics', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmsanalytics', 'controller', 'SeeCMSAnalytics' );
$this->configureViewPart( 'seecmsanalytics', 'controllerMethod', 'loadData' );

/* Add ons */
$this->addViewPart( 'seecmsaddons' );
$this->configureViewPart( 'seecmsaddons', 'path', 'plugin/SeeCMS' );

/* Content editor */
$this->addViewPart( 'seecmseditcontent' );
$this->configureViewPart( 'seecmseditcontent', 'path', 'plugin/SeeCMS' );

$this->addViewPart( 'seecmsselectimage' );
$this->configureViewPart( 'seecmsselectimage', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmsselectimage', 'controller', 'SeeCMSMedia' );
$this->configureViewPart( 'seecmsselectimage', 'controllerMethod', 'selectimageOptions' );
$this->configureViewPart( 'seecmsselectimage', 'controllerPassin', 1 );

$this->addViewPart( 'seecmsselectlink' );
$this->configureViewPart( 'seecmsselectlink', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmsselectlink', 'controller', 'SeeCMSContent' );
$this->configureViewPart( 'seecmsselectlink', 'controllerMethod', 'loadForLinkSelector' );

$this->addViewPart( 'seecmsselecthtml' );
$this->configureViewPart( 'seecmsselecthtml', 'path', 'plugin/SeeCMS' );

/* Site users */
$this->addViewPart( 'seecmssiteusers' );
$this->configureViewPart( 'seecmssiteusers', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmssiteusers', 'controller', 'SeeCMSWebsiteUser' );
$this->configureViewPart( 'seecmssiteusers', 'controllerMethod', 'loadAll' );
$this->configureViewPart( 'seecmssiteusers', 'controllerPassin', array( 'loadADFs' => true ) );

$this->addViewPart( 'seecmssitegroups' );
$this->configureViewPart( 'seecmssitegroups', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmssitegroups', 'controller', 'SeeCMSWebsiteUser' );
$this->configureViewPart( 'seecmssitegroups', 'controllerMethod', 'loadAllGroups' );

$this->addViewPart( 'seecmseditsiteusers' );
$this->configureViewPart( 'seecmseditsiteusers', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmseditsiteusers', 'controller', 'SeeCMSWebsiteUser' );
$this->configureViewPart( 'seecmseditsiteusers', 'controllerMethod', 'loadForEdit' );

$this->addViewPart( 'seecmseditsitegroups' );
$this->configureViewPart( 'seecmseditsitegroups', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmseditsitegroups', 'controller', 'SeeCMSWebsiteUser' );
$this->configureViewPart( 'seecmseditsitegroups', 'controllerMethod', 'loadGroupForEdit' );

$this->addViewPart( 'seecmsimportsiteusers' );
$this->configureViewPart( 'seecmsimportsiteusers', 'path', 'plugin/SeeCMS' );
$this->configureViewPart( 'seecmsimportsiteusers', 'controller', 'SeeCMSWebsiteUser' );
$this->configureViewPart( 'seecmsimportsiteusers', 'controllerMethod', 'import' );
