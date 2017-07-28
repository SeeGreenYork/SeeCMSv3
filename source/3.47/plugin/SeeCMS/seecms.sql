CREATE TABLE IF NOT EXISTS `adf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `objecttype` varchar(32) NOT NULL,
  `objectid` int(11) NOT NULL,
  `cascade` int(11) NOT NULL,
  `contenttype_id` int(11) NOT NULL,
  `exclude` varchar(255) NOT NULL DEFAULT '',
  `identifier` varchar(32) NOT NULL DEFAULT '',
  `theme` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `adfcontent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objecttype` varchar(255) NOT NULL,
  `objectid` int(11) NOT NULL,
  `adf_id` int(11) NOT NULL,
  `content` mediumtext NOT NULL,
  `language` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `adminapproval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objecttype` varchar(32) NOT NULL DEFAULT '',
  `objectid` int(11) NOT NULL DEFAULT '0',
  `complete` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `adminuser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `passwordformat` varchar(12) NOT NULL DEFAULT '',
  `adminuserrole_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `adminusergroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `adminusergrouppermission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objecttype` varchar(255) NOT NULL,
  `objectid` int(11) NOT NULL,
  `adminusergroup_id` int(11) NOT NULL DEFAULT '0',
  `accesslevel` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `adminuserrole` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `config` text NOT NULL,
  `cmsnavigation` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `adminuserrole` (`id`, `name`, `config`, `cmsnavigation`) VALUES
(1, 'Administrator', '{ "ROOT": 5, "action-adminAuthentication-delete": 5, "action-adminAuthentication-deleteGroup": 5, "action-content-apply": 5, "action-content-completeApproval": 5, "action-content-discard": 5, "action-content-edit": 5, "action-content-findSelectedLink": 5, "action-content-prepareSelectedLink": 5, "action-content-requestApproval": 5, "action-download-create": 5, "action-download-delete": 5, "action-download-folderTree": 5, "action-download-loadByFolder": 5, "action-download-loadCreateButtons": 5, "action-download-loadDropzone": 5, "action-download-move": 5, "action-download-savefolder": 5, "action-download-status": 5, "action-export-websiteusers": 5, "action-import-websiteusers": 5, "action-media-create": 5, "action-media-delete": 5, "action-media-folderTree": 5, "action-media-loadByFolder": 5, "action-media-loadCreateButtons": 5, "action-media-loadDropzone": 5, "action-media-move": 5, "action-media-resampleImage": 5, "action-media-savefolder": 5, "action-page-adminTree": 5, "action-page-adminTreeSession": 5, "action-page-create": 5, "action-page-delete": 5, "action-page-move": 5, "action-page-requestApproval": 5, "action-page-status": 5, "action-post-create": 5, "action-post-delete": 5, "action-post-folderTree": 5, "action-post-loadCreateButtons": 5, "action-post-loadByFolder": 5, "action-post-loadOptionsForDialog": 5, "action-post-move": 5, "action-post-savefolder": 5, "action-post-status": 5, "action-post-loadCrea": 5, "action-search-adminSearch": 5, "action-websiteUser-activate": 5, "action-websiteUser-adminSearch": 5, "action-websiteUser-deactivate": 5, "action-websiteUser-delete": 5, "action-websiteUser-deleteGroup": 5, "action-websiteUser-toggleGroupUser": 5, "addons\/": 5, "admin\/": 5, "admin\/groups\/": 5, "admin\/groups\/edit\/": 5, "admin\/settings\/": 5, "admin\/users\/": 5, "admin\/users\/edit\/": 5, "analytics\/": 5, "download\/add\/": 5, "download\/edit\/": 5, "downloads\/": 5, "media\/": 5, "media\/add\/": 5, "media\/edit\/": 5, "page\/edit\/": 5, "pages\/": 5, "popup\/select\/image\/": 5, "popup\/select\/link\/": 5, "post\/edit\/": 5, "posts\/": 5, "siteusers\/": 5, "siteusers\/export\/": 5, "siteusers\/import\/": 5, "siteusers\/viewgroups\/": 5, "siteusers\/viewgroups\/editgroups\/": 5, "siteusers\/viewusers\/": 5, "siteusers\/viewusers\/editusers\/": 5 } ', ' { "0": "Pages", "2": "Posts", "3": "Media", "4": "Downloads", "5": "Site users", "6": "Admin", "7": "Analytics", "8": "Add ons" }');

CREATE TABLE IF NOT EXISTS `adminuser_adminusergroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adminuser_id` int(11) NOT NULL,
  `adminusergroup_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `analyticsvisitor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(32) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `browser` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `posttype_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  `objecttype` VARCHAR(32) NOT NULL DEFAULT 'post',
  `template` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `category_download` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `download_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `category_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objecttype` varchar(255) NOT NULL,
  `objectid` int(11) NOT NULL,
  `contentcontainer_id` int(11) NOT NULL,
  `content` mediumtext NOT NULL,
  `language` varchar(4) NOT NULL,
  `editable` int(11) NOT NULL DEFAULT '1',
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `contentappend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objecttype` varchar(255) NOT NULL,
  `objectid` int(11) NOT NULL,
  `contentcontainer_id` int(11) NOT NULL,
  `content` mediumtext NOT NULL,
  `language` varchar(4) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `contentcontainer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contenttype_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `contenttype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `fields` text NOT NULL,
  `settings` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `contenttype` (`id`, `type`, `fields`, `settings`) VALUES
(1, 'Rich Text', '', '');

CREATE TABLE IF NOT EXISTS `datacache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar( 255 ) NOT NULL,
  `context` text NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `download` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL,
  `isfolder` tinyint(4) NOT NULL DEFAULT '0',
  `parentid` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `uploaded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `imagesize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `mode` varchar(255) NOT NULL,
  `theme` varchar(255) NOT NULL DEFAULT '',
  `identifier` varchar(255) NOT NULL DEFAULT '',
  `settings` text DEFAULT NULL,
  `selectable` INT NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alt` varchar(255) NOT NULL,
  `isfolder` tinyint(4) NOT NULL DEFAULT '0',
  `parentid` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `type` varchar(32) NOT NULL,
  `pathmodifier` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL DEFAULT 'Default',
  `status` int(11) NOT NULL DEFAULT '0',
  `parentid` int(11) NOT NULL DEFAULT '0',
  `pageorder` int(11) NOT NULL DEFAULT '0',
  `deleted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `commencement` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expiry` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastupdated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `visibility` int(11) NOT NULL DEFAULT '1',
  `htmltitle` varchar(255) DEFAULT NULL,
  `metadescription` text,
  `metakeywords` text,
  `redirect` text,
  `ascendants` varchar(255) NOT NULL,
  `site_id` int(11) NULL DEFAULT '0',
  `protected` int(11) NOT NULL DEFAULT '0',
  `clone` text NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL DEFAULT 'Default',
  `date` date NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `parentid` int(11) NOT NULL DEFAULT '0',
  `postorder` int(11) NOT NULL DEFAULT '0',
  `deleted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `commencement` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expiry` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastupdated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `visibility` int(11) NOT NULL DEFAULT '1',
  `htmltitle` varchar(255) NOT NULL DEFAULT '',
  `metadescription` text,
  `metakeywords` text,
  `redirect` text,
  `standfirst` text,
  `isfolder` int(11) NOT NULL DEFAULT '0',
  `tags` varchar(255) NOT NULL DEFAULT '',
  `media_id` int(11) NOT NULL DEFAULT '0',
  `posttype_id` int(11) NOT NULL DEFAULT '1',
  `eventstart` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `eventend` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `posttype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `page_id` INT NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `posttype` (`id`, `name`) VALUES
(1, 'Post'),
(2, 'Event');

CREATE TABLE IF NOT EXISTS `route` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `route` varchar(255) NOT NULL,
  `primaryroute` int(11) NOT NULL,
  `objecttype` varchar(32) NOT NULL,
  `objectid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Finder` (`primaryroute`,`objecttype`,`objectid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `route` (`id`, `route`, `primaryroute`, `objecttype`, `objectid`) VALUES
(1, '/', 1, 'page', 1);

CREATE TABLE IF NOT EXISTS `session` (
  `sessionid` varchar(32) NOT NULL,
  `access` int(10) unsigned DEFAULT NULL,
  `data` mediumtext,
  PRIMARY KEY (`sessionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

INSERT INTO `setting` (`id`, `name`, `value`) VALUES
(1, 'email', '<html xmlns="http://www.w3.org/1999/xhtml">\r\n<head>\r\n<title></title>\r\n<!-- hotmail fix start -->\r\n<style type="text/css">\r\n.ReadMsgBody\r\n{ width: 100%;}\r\n.ExternalClass\r\n{width: 100%;}\r\np {margin-bottom: 0em;}\r\na {color: #0073c9;}\r\n</style>\r\n<!-- hotmail fix end -->\r\n</head>\r\n<body style="background: #fff;">\r\n<table cellspacing="0" cellpadding="0" width="100%" border="0" style="width: 100%; background: #fff; font-family: arial, sans-serif">\r\n<tr>\r\n<td>\r\n<EMAILCONTENT>\r\n</td>\r\n</tr>\r\n</table>\r\n</body>\r\n</html>'),
(2, 'version', '3.47'),
(3, 'editBarV2', '1'),
(4, 'friendlyImageURLs', '1');

CREATE TABLE IF NOT EXISTS `site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL DEFAULT '',
  `homeroute` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `systemlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objectType` varchar(255) NOT NULL,
  `objectID` int(11) NOT NULL,
  `event` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `websiteuser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL DEFAULT '',
  `forename` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `organisation` varchar(255) NOT NULL DEFAULT '',
  `jobtitle` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `password` text NOT NULL,
  `passwordformat` varchar(12) NOT NULL,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) NOT NULL DEFAULT '',
  `address3` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL,
  `region` varchar(255) NOT NULL,
  `postcode` varchar(16) NOT NULL,
  `country` varchar(32) NOT NULL DEFAULT '',
  `activation` varchar(255) NOT NULL DEFAULT '',
  `passwordrecovery` varchar(255) NOT NULL DEFAULT '',
  `creationdate` datetime NOT NULL,
  `modifieddate` datetime NOT NULL,
  `logindate` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `websiteusergroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `autoaddnewusers` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `websiteusergrouppermission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objecttype` varchar(255) NOT NULL,
  `objectid` int(11) NOT NULL,
  `websiteusergroup_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `websiteuser_websiteusergroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `websiteuser_id` int(11) NOT NULL,
  `websiteusergroup_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;