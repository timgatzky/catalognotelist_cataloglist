-- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************


-- --------------------------------------------------------

-- 
-- Table `tl_module`
-- 
CREATE TABLE `tl_module` (
	`catalognotelist_catalogs` blob NULL,
	`catalognotelist_visible` blob NULL,
	`catalognotelist_link_override` char(1) NOT NULL default '',
	`catalognotelist_islink` blob NULL,
	`catalognotelist_thumbnails_override` char(1) NOT NULL default '',
	`catalognotelist_imagemain_field` blob NULL,
	`catalognotelist_imagemain_size` varchar(255) NOT NULL default '',
	`catalognotelist_imagemain_fullsize` char(1) NOT NULL default '',
	`catalognotelist_list_use_limit` char(1) NOT NULL default '',
	`catalognotelist_list_offset` smallint(5) NOT NULL default '0',
	`catalognotelist_limit` varchar(32) NOT NULL default '',
 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
