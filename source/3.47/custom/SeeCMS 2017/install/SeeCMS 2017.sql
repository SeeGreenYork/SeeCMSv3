INSERT INTO `adf` (`id`, `title`, `objecttype`, `objectid`, `cascade`, `contenttype_id`, `exclude`, `identifier`, `theme`) VALUES
(1, 'Banners', 'page', 1, 0, 2, '', '', 'SeeCMS 2017'),
(2, 'Footer Contact Information', 'page', 1, 0, 3, '', '', 'SeeCMS 2017'),
(3, 'Banner Image', 'page', 0, 1, 4, '1', '', 'SeeCMS 2017');

INSERT INTO `contentcontainer` (`id`, `contenttype_id`) VALUES
(1, 1),
(2, 1),
(3, 1);

INSERT INTO `page` (`id`, `title`, `template`, `status`, `parentid`, `pageorder`, `deleted`, `commencement`, `expiry`, `lastupdated`, `visibility`, `htmltitle`, `metadescription`, `metakeywords`, `redirect`, `ascendants`) VALUES
(1, 'Home', 'Home', 1, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '', '', '', '', '0');


INSERT INTO `setting` (`name`, `value`) VALUES ('pagetemplates', '["Default", "Home"]');