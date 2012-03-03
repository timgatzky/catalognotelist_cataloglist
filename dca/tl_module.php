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
 * @copyright  Tim Gatzky 2011, Catalog Team 
 * @author     Tim Gatzky <info@tim-gatzky.de>
 * @package    Catalog 
 * @license    LGPL 
 * @filesource
 */


/**
 * Palettes
 */
#$GLOBALS['TL_DCA']['tl_module']['palettes']['cataloglist_notelist']  = '{title_legend},name,headline,type;{config_legend},catalognotelist_catalogs,catalognotelist_visible,catalognotelist_link_override;{catalog_filter_legend:hide},perPage,catalognotelist_list_use_limit,catalog_order;{catalog_thumb_legend:hide},catalognotelist_thumbnails_override;{template_legend:hide},catalog_template,catalog_layout;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

// without limit and offset
$GLOBALS['TL_DCA']['tl_module']['palettes']['cataloglist_notelist']  = '{title_legend},name,headline,type;{config_legend},catalognotelist_catalogs,catalognotelist_visible,catalognotelist_link_override;{catalog_thumb_legend:hide},catalognotelist_thumbnails_override;{template_legend:hide},catalog_template,catalog_layout;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
#$GLOBALS['TL_DCA']['tl_module']['palettes']['cataloglist_notelist']  = '{title_legend},name,headline,type;{config_legend},catalognotelist_catalogs,catalognotelist_visible;{template_legend:hide},catalog_template,catalog_layout;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


/**
 * Selectors
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalognotelist_link_override';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalognotelist_list_use_limit';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalognotelist_thumbnails_override';



/**
 * Subpalettes
 */
array_insert($GLOBALS['TL_DCA']['tl_module']['subpalettes'], 1, array
(
	'catalognotelist_link_override' 		=> 'catalognotelist_islink',
	'catalognotelist_list_use_limit'		=> 'catalognotelist_list_offset,catalognotelist_limit',
	'catalognotelist_thumbnails_override'	=> 'catalognotelist_imagemain_field,catalognotelist_imagemain_size,catalognotelist_imagemain_fullsize',	
));


/**
 * Fields
 */ 
array_insert($GLOBALS['TL_DCA']['tl_module']['fields'] , 1, array
(

	'catalognotelist_catalogs' => array
	(
		'label'					=> &$GLOBALS['TL_LANG']['tl_module']['catalognotelist_catalogs'],
		'exclude'				=> false,
		'inputType'				=> 'checkbox',
		'options_callback'		=> array('tl_module_cataloglist_notelist', 'getCatalogSelectList'),
		'eval'					=> array('mandatory'=> false, 'submitOnChange'=> true, 'multiple'=> true ),
	),
	
	'catalognotelist_visible' => array
	(
		'label'					=> &$GLOBALS['TL_LANG']['tl_module']['catalog_visible'],
		'exclude'               => true,
		'inputType'				=> 'checkboxWizard',
		'options_callback'		=> array('tl_module_cataloglist_notelist', 'getCatalogFieldsUngroupedList'),
		'eval'					=> array('mandatory'=> false, 'multiple'=>true),
	),
	
	'catalognotelist_link_override' => array
	(
		'label'                 => &$GLOBALS['TL_LANG']['tl_module']['catalog_link_override'],
		'exclude'               => true,
		'inputType'             => 'checkbox',
		'eval'					=> array('submitOnChange'=> true),
	),
	
	'catalognotelist_islink' => array
	(
		'label'                 => &$GLOBALS['TL_LANG']['tl_module']['catalog_islink'],
		'exclude'               => true,
		'inputType'             => 'checkbox',
		'options_callback'      => array('tl_module_cataloglist_notelist', 'getCatalogFieldsUngroupedList'),
		'eval'                  => array('multiple'=> true)
	),
	
	'catalognotelist_list_use_limit' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['catalog_list_use_limit'],
		'exclude'                 => true,
		'inputType'               => 'checkbox',
		'eval'                    => array('submitOnChange'=> true, 'tl_class' => 'clr'),
	),
	
	'catalognotelist_limit' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['catalog_limit'],
		'exclude'                 => true,
		'inputType'               => 'text',
		'default'               	=> '1',
		'eval'                    => array('rgxp'=>'digit')
	),
	
	'catalognotelist_list_offset' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['catalog_list_offset'],
		'exclude'                 => true,
		'inputType'               => 'text',
		'default'                 => '10',
		'eval'                    => array('rgxp' => 'decimal', 'tl_class'=>'w50'),
	),
	
	'catalognotelist_thumbnails_override' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['catalog_thumbnails_override'],
		'exclude'                 => true,
		'inputType'               => 'checkbox',
		'eval'					  => array('submitOnChange'=> true),
	),

	'catalognotelist_imagemain_field' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['catalog_imagemain_field'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'options_callback'        => array('tl_module_cataloglist_notelist', 'getImageFields'),
		'eval'                    => array('multiple'=> true, 'includeBlankOption' => true, 'tl_class'=>'w50')
	),

	'catalognotelist_imagemain_size' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['catalog_imagemain_size'],
		'exclude'                 => true,
		'inputType'               => 'imageSize',
		'options'                 => array('crop', 'proportional', 'box'),
		'reference'               => &$GLOBALS['TL_LANG']['MSC'],
		'eval'                    => array('rgxp'=>'digit', 'nospace'=>true)
	),

	'catalognotelist_imagemain_fullsize' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['catalog_imagemain_fullsize'],
		'exclude'                 => true,
		'inputType'               => 'checkbox',
	),
	
));



/**
 * Class tl_module_catalog
 *
 * @copyright  Tim Gatzky 2011 
 * @author     Tim Gatzky <info@tim-gatzky.de>
 * @package    Controller
 */
class tl_module_cataloglist_notelist extends Backend
{
	/** 
	 * Get values of catalog checkboxes
	 * @return array
	 */
	public function getCatalogSelectValue(DataContainer $dc)
	{
		$objField = $this->Database->prepare("SELECT catalognotelist_catalogs FROM tl_module WHERE id=?")
				->limit(1)
				->execute($this->Input->get('id'));
		if(!$objField->numRows) return;
		
		$arrValues = deserialize($objField->catalognotelist_catalogs);
		
		return $arrValues;
		
	}
	
	/**
	 * Get all catalog fields and return them as array
	 * @return array, multidimensional
	 */
	public function getCatalogFields(DataContainer $dc, $arrTypes=false, $blnImage=false, $grouped=false)
	{
		if(!$arrTypes)
			$arrTypes = $GLOBALS['BE_MOD']['content']['catalog']['typesCatalogFields'];
			
		$catalogs = $this->getCatalogSelectList($dc);
		
		if(!$this->getCatalogSelectValue($dc)) return;
		$catalogs = array_values($this->getCatalogSelectValue($dc));
		
		$fields = array();
		$chkImage = $blnImage ? " AND c_fields.showImage=1 " : "";
		
		$arrFields = array();
		foreach($catalogs as $catalog)
		{	
			$objFields = $this->Database->prepare("SELECT c_fields.*, c_types.*". 
				" FROM tl_catalog_fields AS c_fields, tl_catalog_types AS c_types, tl_module AS m ".
				" WHERE c_fields.pid=$catalog AND c_fields.type IN ('" . implode("','", $arrTypes) . "')".$chkImage."AND c_types.id=$catalog AND m.id=? ORDER BY c_fields.pid DESC" )
				->execute($this->Input->get('id'));
			
			if(!$objFields->numRows) continue ;
			while($objFields->next())
			{
				// parse '" COLNAME "' around the column name to seperate the name later
				// -> workaround for duplicate table names
				$value = $objFields->name . ': ' . '[' .'"'.$objFields->colName.'"'. ':' .$objFields->type. ']';
				
				if(!$grouped)
				{
					$fields[$objFields->colName] = $value;
					$arrFields[] = $value; // create large list and ignore duplicate field names
				}
				else
				{
					// create a multidimensional array.
					$value = $objFields->name . ': ' . '[' .$objFields->colName. ':' .$objFields->type. ']';
					$fields[$objFields->name][] = $value;
				}
			}

		}
		
		$fields = $arrFields;
		
		return $fields;

	}
	
	/**
	 * Get all linkable fields and return them as array
	 * @return array
	 */
	public function getCatalogLinkFields(DataContainer $dc)
	{
		return $this->getCatalogFields($dc, $GLOBALS['BE_MOD']['content']['catalog']['typesLinkFields']);
	}
	
	/**
	 * Get all image fields and return them as array
	 * @return array
	 */
	public function getImageFields(DataContainer $dc)
	{
		return $this->getCatalogFields($dc, array('file'), true);
	}
	
	/**
	 * Get fields and return them as sorted 1-dimensional array
	 * @return array
	 */
	public function getCatalogFieldsUngroupedList(DataContainer $dc)
	{
		return $this->getCatalogFields($dc, false, false);
	}
	
	/**
	 * Get fields and return them as sorted 1-dimensional array
	 * @return array
	 */
	public function getCatalogFieldsGroupedList(DataContainer $dc)
	{
		return $this->getCatalogFields($dc, false, false);
	}
	
			
	/**
	 * Get catalogs
	 * @return array
	 */
	public function getCatalogSelectList(DataContainer $dc)
	{
		$catalogs = array();
		$objFields = $this->Database->prepare("SELECT * FROM tl_catalog_types ORDER BY name ASC")
				->execute();
		if(!$objFields->numRows) return array();
		
		while ($objFields->next())
		{
			$catalogs[$objFields->id] = $objFields->name;
			$ids = $objFields->id;
		}
		
		return $catalogs;
	
	}
	
}


?>