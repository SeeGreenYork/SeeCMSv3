<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

/*********************************************/
/************** STATIC ROUTING ***************/
/*********************************************/

//pages
$this->addRoute( "{$plugin->cmsRoot}/", "Pages" );
$this->configureRoute( 'redirect', 'pages/' );

$this->addRoute( "{$plugin->cmsRoot}/pages/", "Pages" );
$this->configureRoute( "template", "SeeCMS/Pages" );
$this->configureRoute( "id", "pages" );

$this->addRoute( "{$plugin->cmsRoot}/page/create/", "Create a page" );
$this->configureRoute( "template", "SeeCMS/Create page" );
$this->configureRoute( "id", "createpage" );
$this->configureRoute( "invisible", 1 );

$this->addRoute( "{$plugin->cmsRoot}/page/move/", "Move a page" );
$this->configureRoute( "template", "SeeCMS/Move page" );
$this->configureRoute( "id", "movepage" );
$this->configureRoute( "invisible", 1 );

$this->addRoute( "{$plugin->cmsRoot}/page/edit/", "Edit page" );
$this->configureRoute( "template", "SeeCMS/Edit Page" );
$this->configureRoute( "id", "editpage" );
$this->configureRoute( "invisible", 1 );

//posts
$this->addRoute( "{$plugin->cmsRoot}/posts/", "Posts" );
$this->configureRoute( "template", "SeeCMS/Posts" );
$this->configureRoute( "id", "news" );

$this->addRoute( "{$plugin->cmsRoot}/post/edit/", "Edit post" );
$this->configureRoute( "template", "SeeCMS/Edit Post" );
$this->configureRoute( "id", "editpost" );
$this->configureRoute( "invisible", 1 );

//media
$this->addRoute( "{$plugin->cmsRoot}/media/", "Media" );
$this->configureRoute( "template", "SeeCMS/Media" );
$this->configureRoute( "id", "images" );

$this->addRoute( "{$plugin->cmsRoot}/media/add/", "SeeCMS Add media" );
$this->configureRoute( "routeToController", "SeeCMSMedia" );
$this->configureRoute( "routeToMethod", "create" );
$this->configureRoute( "invisible", 1 );

$this->addRoute( "{$plugin->cmsRoot}/media/edit/", "Edit media" );
$this->configureRoute( "template", "SeeCMS/Edit Media" );
$this->configureRoute( "id", "editmedia" );
$this->configureRoute( "invisible", 1 );

//downloads
$this->addRoute( "{$plugin->cmsRoot}/downloads/", "Downloads" );
$this->configureRoute( "template", "SeeCMS/Downloads" );
$this->configureRoute( "id", "downloads" );

$this->addRoute( "{$plugin->cmsRoot}/download/add/", "SeeCMS Add download" );
$this->configureRoute( "routeToController", "SeeCMSDownload" );
$this->configureRoute( "routeToMethod", "create" );
$this->configureRoute( "invisible", 1 );

$this->addRoute( "{$plugin->cmsRoot}/download/edit/", "Edit download" );
$this->configureRoute( "template", "SeeCMS/Edit Download" );
$this->configureRoute( "id", "editdownload" );
$this->configureRoute( "invisible", 1 );

//siteusers
$this->addRoute( "{$plugin->cmsRoot}/siteusers/", "Site users" );
$this->configureRoute( "redirect", "viewusers/" );

$this->addRoute( "{$plugin->cmsRoot}/siteusers/viewusers/", "View users" );
$this->configureRoute( "template", "SeeCMS/Site Users" );
$this->configureRoute( "id", "viewusers" );

$this->addRoute( "{$plugin->cmsRoot}/siteusers/viewusers/editusers", "Edit users" );
$this->configureRoute( "template", "SeeCMS/Edit Users" );
$this->configureRoute( "id", "editusers" );

$this->addRoute( "{$plugin->cmsRoot}/siteusers/viewgroups/", "View groups" );
$this->configureRoute( "template", "SeeCMS/Site Groups" );
$this->configureRoute( "id", "viewgroups" );

$this->addRoute( "{$plugin->cmsRoot}/siteusers/viewgroups/editgroups", "Edit groups" );
$this->configureRoute( "template", "SeeCMS/Edit Groups" );
$this->configureRoute( "id", "editgroups" );

$this->addRoute( "{$plugin->cmsRoot}/siteusers/export/", "Export site users" );
$this->configureRoute( "routeToController", "SeeCMSWebsiteUser" );
$this->configureRoute( "routeToMethod", "export" );

$this->addRoute( "{$plugin->cmsRoot}/siteusers/import/", "Import site users" );
$this->configureRoute( "template", "SeeCMS/Import Site Users" );
$this->configureRoute( "id", "importsiteusers" );

//admin
$this->addRoute( "{$plugin->cmsRoot}/admin/", "Admin" );
$this->configureRoute( "redirect", "users/" );

$this->addRoute( "{$plugin->cmsRoot}/admin/users/", "Users" );
$this->configureRoute( "template", "SeeCMS/Admin" );
$this->configureRoute( "id", "adminusers" );

$this->addRoute( "{$plugin->cmsRoot}/admin/users/edit", "Edit Admin Users" );
$this->configureRoute( "template", "SeeCMS/Edit Admin User" );
$this->configureRoute( "id", "editadmin" );

$this->addRoute( "{$plugin->cmsRoot}/admin/groups/", "Groups" );
$this->configureRoute( "template", "SeeCMS/Admin Groups" );
$this->configureRoute( "id", "viewgroups" );

$this->addRoute( "{$plugin->cmsRoot}/admin/groups/edit", "Edit groups" );
$this->configureRoute( "template", "SeeCMS/Edit Admin Group" );
$this->configureRoute( "id", "editgroups" );

$this->addRoute( "{$plugin->cmsRoot}/admin/cmsupdate/", "CMS Update" );
$this->configureRoute( "template", "SeeCMS/CMS Update" );
$this->configureRoute( "id", "cmsupdate" );


//analytics
$this->addRoute( "{$plugin->cmsRoot}/analytics/", "Analytics" );
$this->configureRoute( "template", "SeeCMS/Analytics" );
$this->configureRoute( "id", "analytics" );

//addons
$this->addRoute( "{$plugin->cmsRoot}/addons/", "Add ons" );
$this->configureRoute( "template", "SeeCMS/Add Ons" );
$this->configureRoute( "id", "addons" );

//editor
$this->addRoute( "{$plugin->cmsRoot}/popup/select/image/", "Select image" );
$this->configureRoute( "template", "SeeCMS/Popup Select Image" );
$this->addRoute( "{$plugin->cmsRoot}/popup/select/link/", "Select link" );
$this->configureRoute( "template", "SeeCMS/Popup Select Link" );
$this->addRoute( "{$plugin->cmsRoot}/popup/select/html/", "Select HTML" );
$this->configureRoute( "template", "SeeCMS/Popup Select HTML" );

//login
$this->addRoute( "{$plugin->cmsRoot}/login/", "SeeCMS Login" );
$this->configureRoute( "template", "SeeCMS/Login" );
$this->configureRoute( "invisible", 1 );

//ajax
$this->addRoute( "{$plugin->cmsRoot}/ajax/", "SeeCMS Ajax" );
$this->configureRoute( "routeToController", "SeeCMSAjax" );
$this->configureRoute( "routeToMethod", "request" );
$this->configureRoute( "invisible", 1 );

//file download
$this->addRoute( "seecmsfile/", "SeeCMS File" );
$this->configureRoute( "routeToController", "SeeCMSDownload" );
$this->configureRoute( "routeToMethod", "download" );
$this->configureRoute( "invisible", 1 );

//xml sitemap
$this->addRoute( "xmlsitemap/", "SeeCMS XML Sitemap" );
$this->configureRoute( "routeToController", "SeeCMSHelper" );
$this->configureRoute( "routeToMethod", "xmlsitemap" );
$this->configureRoute( "invisible", 1 );