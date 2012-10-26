<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Tim Gatzky 2011 
 * @author     Tim Gatzky <info@tim-gatzky.de>
 * @package    catalognotelist_cataloglist
 * @license    LGPL 
 * @filesource
 */


$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][]  = array('tl_module_cataloglist_notelist','modifyFieldDca');


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['cataloglist_notelist']  = '{title_legend},name,headline,type;{config_legend},catalognotelist_catalogs,catalog_visible,catalog_link_override;{catalog_thumb_legend:hide},catalog_thumbnails_override;{template_legend:hide},catalog_template,catalog_layout;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

/**
 * Fields
 */ 
$GLOBALS['TL_DCA']['tl_module']['fields']['catalognotelist_catalogs'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['catalognotelist_catalogs'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'options_callback'		=> array('tl_module_cataloglist_notelist', 'getCatalogs'),
	'eval'					=> array('mandatory'=> false, 'submitOnChange'=> true, 'multiple'=> true ),
);
	
$GLOBALS['TL_DCA']['tl_module']['fields']['catalognotelist_imagemain_field'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['catalog_imagemain_field'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_module_cataloglist_notelist', 'getImageFields'),
	'eval'                    => array('includeBlankOption' => true, 'multiple' => true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['catalognotelist_imagegallery_field'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['catalog_imagegallery_field'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_module_cataloglist_notelist', 'getGalleryFields'),
	'eval'                    => array('includeBlankOption' => true, 'tl_class' => 'clr w50', 'multiple' => true)
);


class tl_module_cataloglist_notelist extends Backend
{
	/**
	 * Modify field dca depending on loaded module to load different options_callbacks
	 * @return object, DataContainer
	 */
	public function modifyFieldDca(DataContainer $dc)
	{
		$objModule = $this->Database->prepare("SELECT type FROM tl_module WHERE id=?")
									->limit(1)
									->execute($dc->id);
		
		if($objModule->type == 'cataloglist_notelist')
		{
			$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalog_thumbnails_override'] = str_replace('catalog_imagemain_field','catalognotelist_imagemain_field',$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalog_thumbnails_override']);
			$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalog_thumbnails_override'] = str_replace('catalog_imagegallery_field','catalognotelist_imagegallery_field',$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalog_thumbnails_override']);
			
			$GLOBALS['TL_DCA']['tl_module']['fields']['catalog_visible']['options_callback'] = array('tl_module_cataloglist_notelist', 'getCatalogFields');
			$GLOBALS['TL_DCA']['tl_module']['fields']['catalog_islink']['options_callback'] = array('tl_module_cataloglist_notelist', 'getCatalogFields');
		}
		
		return $dc;		
	}
	
	
	/**
	 * Get catalogs
	 * @return array
	 */
	public function getCatalogs()
	{
		$objCatalogs = $this->Database->execute("SELECT * FROM tl_catalog_types ORDER BY id ASC");
		
		if(!$objCatalogs->numRows)
		{
			return array();
		}
		
		$arrReturn = array();
		while ($objCatalogs->next())
		{
			$arrReturn[$objCatalogs->id] = $objCatalogs->name . ' (id:'.$objCatalogs->id.')';
		}
		
		return $arrReturn;
	}
	
	/**
	 * Get catalog fields in selected catalogs
	 * @return array
	 */
	public function getCatalogFields(DataContainer $dc, $arrTypes=false, $arrWhere=array() )
	{
		if(!$dc->activeRecord->catalognotelist_catalogs)
		{
			return array();
		}
		
		$strWhere = '';
		if(count($arrWhere))
		{
			foreach($arrWhere as $k => $v)
			{
				if(!$v) 
				{
					$v = 0;
				}
				$strWhere .= ' AND ' . $k . '=' . $v;
			}			
		}
		
		$objCatalogFields = $this->Database->prepare("
			SELECT * FROM tl_catalog_fields 
			WHERE pid IN(".implode(',', deserialize($dc->activeRecord->catalognotelist_catalogs)).")
			".($arrTypes ? " AND type IN(".implode(',',$arrTypes).")": "")."
			".$strWhere."
			ORDER BY pid,colName ASC")->execute();
		
		if(!$objCatalogFields->numRows)
		{
			return array();
		}	
		
		$arrFields = array();
		while($objCatalogFields->next())
		{
			// Get catalog name
			$objCatalog = $this->Database->prepare("SELECT id,name FROM tl_catalog_types WHERE id=?")->limit(1)->execute($objCatalogFields->pid);
			
			#$arrFields[$objCatalogFields->id.'_'.$objCatalog->id] = $objCatalog->name .": " . $objCatalogFields->name . ' ['.$objCatalogFields->colName.':'.$objCatalogFields->type.']';
			$arrFields[$objCatalog->id.'_'.$objCatalogFields->id.'_'.$objCatalogFields->colName] = $objCatalog->name .": " . $objCatalogFields->name . ' ['.$objCatalogFields->colName.':'.$objCatalogFields->type.']';
		}
		
		ksort($arrFields);
		
		return $arrFields;
	}
	
	/**
	 * Get image fields
	 * @return array
	 */
	public function getImageFields(DataContainer $dc)
	{
		return $this->getCatalogFields($dc, array("'file'"), array('multiple'=>false) );
	}
	
	/**
	 * Get image fields
	 * @return array
	 */
	public function getGalleryFields(DataContainer $dc)
	{
		return $this->getCatalogFields($dc, array("'file'"), array('multiple'=>true) );
	}
}



?>