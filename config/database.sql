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
	`catalognotelist_imagemain_field` blob NULL,
	`catalognotelist_imagegallery_field` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
