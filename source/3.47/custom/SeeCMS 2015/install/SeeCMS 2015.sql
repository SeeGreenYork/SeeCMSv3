INSERT INTO `adf` (`id`, `title`, `objecttype`, `objectid`, `cascade`, `contenttype_id`, `exclude`, `identifier`, `theme`) VALUES
(1, 'Banners', 'page', 1, 0, 2, '', 'SeeCMS 2015 banner', 'SeeCMS 2015');

INSERT INTO `contentcontainer` (`id`, `contenttype_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1);

INSERT INTO `contenttype` (`id`, `type`, `fields`, `settings`) VALUES
(2, 'ADF', 'bannerimage,Image,image,1\nbannertitle,Title,text\nbannertext,Text,richText\nbannerlink,Link,link', 'repeatable=true,limit=6,title=Banners');

INSERT INTO `imagesize` (`id`, `name`, `width`, `height`, `mode`, `theme`) VALUES
(1, 'Banner image', 2000, 550, 'crop', 'SeeCMS 2015'),
(2, 'Event image', 255, 255, 'crop', 'SeeCMS 2015'),
(3, 'Large news thumb', 570, 500, 'crop', 'SeeCMS 2015'),
(4, 'Event thumb', 402, 266, 'crop', 'SeeCMS 2015'),
(5, 'Left column image', 285, 285, 'resize', 'SeeCMS 2015'),
(6, 'Right column image', 855, 855, 'resize', 'SeeCMS 2015');

INSERT INTO `page` (`id`, `title`, `template`, `status`, `parentid`, `pageorder`, `deleted`, `commencement`, `expiry`, `lastupdated`, `visibility`, `htmltitle`, `metadescription`, `metakeywords`, `redirect`, `ascendants`) VALUES
(1, 'Home', 'Home', 1, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '', '', '', '', '0');

INSERT INTO `route` (`route`, `primaryroute`, `objecttype`, `objectid`) VALUES
('/', 1, 'page', 1);

INSERT INTO `setting` (`name`, `value`) VALUES ('pagetemplates', '["Default","Home","50 50","Single","Default (Editable Right Hand Column)"]');