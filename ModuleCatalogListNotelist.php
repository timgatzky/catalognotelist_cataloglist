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
 * @package    Catalog 
 * @license    LGPL 
 * @filesource
 */


class ModuleCatalogListNotelist extends ModuleCatalog
{
	/**
	 * Template
	 */
	protected $strTemplate = 'mod_cataloglist_notelist';
	
	/**
	 * BE Wildcard
	 */
	public function generate()
	{
				
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			
			if ($GLOBALS['TL_LANGUAGE'] == 'de')
			{
				$objTemplate->wildcard  = "### KATALOG-MERKLISTE ###";
			}
			else
			{
			  	$objTemplate->wildcard  = "### CATALOG-NOTELISTE ###";
			}
			
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			if (version_compare(VERSION.'.'.BUILD, '2.9.0', '>='))
				$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
			else
				$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;
			return $objTemplate->parse();
		}
		
		// Fallback template
		if (!strlen($this->catalog_layout))
			$this->catalog_layout = $this->strTemplate;
		
		$this->strTemplate = $this->catalog_layout;
		
		
		$this->catalognotelist_visible = deserialize($this->catalognotelist_visible);
		$this->catalognotelist_catalogs = deserialize($this->catalognotelist_catalogs); 	
		$this->catalog_visible = $this->catalognotelist_visible;
		
		$this->import('Catalog');
		foreach($this->catalognotelist_catalogs as $catalog)
		{
			$objFields = $this->Database->prepare("SELECT id, name, tableName, aliasField FROM tl_catalog_types WHERE id=?")
							->execute($catalog);
			if(!$objFields->numRows) return;
			
			$this->strTable = $objFields->tableName;
			$this->catalog = $catalog;
		 	
		 	// Run routine from Catalog.php for each catalog in the list to get the current DCA
		 	
			// get DCA
			$objCatalog = $this->Database->prepare('SELECT * FROM tl_catalog_types WHERE id=?')
					->limit(1)
					->execute($this->catalog);
			
			if ($objCatalog->numRows > 0 && $objCatalog->tableName)
			{
				$this->strTable = $objCatalog->tableName;
				$this->strAliasField=$objCatalog->aliasField;
				$this->publishField=$objCatalog->publishField;
				 
				// dynamically load dca for catalog operations
				$this->Import('Catalog');
				if(!$GLOBALS['TL_DCA'][$objCatalog->tableName]['Cataloggenerated'])
				{
					// load default language
					$GLOBALS['TL_LANG'][$objCatalog->tableName] = is_array($GLOBALS['TL_LANG'][$objCatalog->tableName])
														 ? self::array_replace_recursive($GLOBALS['TL_LANG']['tl_catalog_items'], $GLOBALS['TL_LANG'][$objType->tableName])
														 : $GLOBALS['TL_LANG']['tl_catalog_items'];
					// load dca
					$GLOBALS['TL_DCA'][$objCatalog->tableName] = 
						is_array($GLOBALS['TL_DCA'][$objCatalog->tableName])
							? Catalog::array_replace_recursive($this->Catalog->getCatalogDca($this->catalog), $GLOBALS['TL_DCA'][$objCatalog->tableName])
							: $this->Catalog->getCatalogDca($this->catalog);
					$GLOBALS['TL_DCA'][$objCatalog->tableName]['Cataloggenerated'] = true;
				}
			}
		}
		
		
		return parent::generate();
	}
	
		
	/**
	 * Generate module, make non-abstract
	 */
	protected function compile()
	{
		$arrCatalogs = deserialize($this->catalognotelist_catalogs); 	// list of all catalogs (id) selected by the user
		#$arrVisibles = deserialize($this->catalognotelist_visible); 	// list of all fields selected by the user
		
		// Imports
		$this->import('Database');
		$this->import('Session');
		
		// Collect session data
		$arrSessionData = $this->Session->getData();
		$arrNotelist = $arrSessionData['catalog_notelist'];
		
		// parse empty template if notelist is not created yet
		if(!$arrNotelist || !count($arrNotelist))
		{
			$objTemplate = new FrontendTemplate($this->catalog_template);
			$objTemplate->entries = array();
			$this->Template->catalog = $objTemplate->parse();
			$this->Template->total = 0;
		
			return;
		} 
		
		// clean up
		foreach($arrNotelist as $catid => $values)
		{
			// check if catalog is unchecked in module settings but still in notelist
			if(!in_array($catid, $arrCatalogs)) unset($arrNotelist[$catid]);
			// check if catalog is checked in module but empty in notelist
			if(!count($values)) unset($arrNotelist[$catid]);
		}
		
		// parse empty template if notelist was created but contains no items
		if(!$arrNotelist || !count($arrNotelist))
		{
			$objTemplate = new FrontendTemplate($this->catalog_template);
			$objTemplate->entries = array();
			$this->Template->catalog = $objTemplate->parse();
			$this->Template->total = 0;
			return;
		}
				
//---------------------------------------------------------------------------------
		
		$this->Template->catalog = ''; // clear template
		
		// collect information about the selected catalogs
		$objFields = $this->Database->prepare("SELECT id, name, tableName, aliasField FROM tl_catalog_types WHERE id IN(" . implode(',', deserialize($this->catalognotelist_catalogs)) . ")")
						->execute();
		if(!$objFields->numRows) return;
		
		$arrTables = array();
		while($objFields->next() )
		{
			$arrTables[$objFields->id] = array (
				'strTable' 		=> $objFields->tableName,
				'aliasField'	=> $objFields->aliasField,
				'name'			=> $objFields->name
			);
			$this->strTable = $objFields->tableName;
			$this->strAliasField = $objFields->aliasField;
		}

//--
		
		// local members
		$arrEntries = array();
		$arrCatalogFields = array();
		$arrEntryIds = array();
		$arrAmount = array();
		$arrVisibles = array();	
		$total = 0;
		$index = 0;
		$totalAmount = 0;
		foreach($arrNotelist as $catid => $notelistValues)
		{
			// get ids of entries that should be displayed
			foreach($notelistValues as $value)
			{
				$arrEntryIds[$catid][]				= $value['id'];
				$arrAmount[$catid][$value['id']]	= $value['amount'];
			}
			
			$objFields = $this->Database->prepare("SELECT * FROM " .$arrTables[$catid]['strTable']. " WHERE pid=?")
							->limit(1)
							->execute($catid)
							->fetchAssoc(); // @return array
			if(!$objFields || !count($objFields)) return;
			
			/**
			 * workaround for duplicate field names in different catalogs
			 */
			
			// seperate colName from checkbox value
			$arrVisibles = $this->getColNameFromArray(deserialize($this->catalognotelist_visible));
			
			// create unique list of visible fields for the current catalog
			$arrVisibles = array();
			$name = $arrTables[$catid]['name'];
			foreach(deserialize($this->catalognotelist_visible) as $field)
			{
				$catName = $this->getNameFromArray(array($field));
				$cols = array_unique($this->getColNameFromArray(array($field)));
				
				if(strstr($catName[0], $name))
				{
					$arrVisibles[$catid][] = $cols[0];
				}
			}	
			
			// kick duplicated field names out to minimize the sql query
			$arrCatalogFields[$catid] = array_intersect(array_unique($arrVisibles[$catid]), array_keys($objFields));

//--
			
			// create unique link overwrite list for the current catalog
			$arrIsLink = array();
			$name = $arrTables[$catid]['name'];
			
			if($this->catalognotelist_link_override)
			{
				foreach(deserialize($this->catalognotelist_islink) as $field)
				{
					$catName = $this->getNameFromArray(array($field));
					$cols = array_unique($this->getColNameFromArray(array($field)));
					
					if(strstr($catName[0], $name))
					{
						$arrIsLink[$catid][] = $cols[0];
					}
				
				}	
			}
//--
			
			// overwrite cataloglist process variables
			$this->strTable = $arrTables[$catid]['strTable'];
			$this->strAliasField = $arrTables[$catid]['aliasField'];
			$this->catalog_visible = $arrCatalogFields[$catid];
			$this->catalog = $catid;
			$this->catalog_link_override = $this->catalognotelist_link_override;
			$this->catalog_islink = $arrIsLink[$catid];
			
			// create sql query array
			$arrQuery = $this->processFieldSQL($arrCatalogFields[$catid]);
			
			// need alias to let catalog generate the url to the reader site. will be kicked out later.
			if($this->strAliasField)
				$arrQuery[] = $this->strAliasField;
			
			$objCatalogStmt = $this->Database->prepare("SELECT " .implode(',',$this->systemColumns). "," .implode(',',$arrQuery).", (SELECT name FROM tl_catalog_types WHERE tl_catalog_types.id=".$this->strTable.".pid) AS catalog_name, (SELECT jumpTo FROM tl_catalog_types WHERE tl_catalog_types.id=".$this->strTable.".pid) AS parentJumpTo".
					" FROM " .$this->strTable.
					" WHERE id IN(" .implode(',', $arrEntryIds[$catid]). ")".
					" ORDER BY sorting") ;
			
			$objCatalog = $objCatalogStmt->execute(); // fire sql
			
			// total
			$total += $objCatalog->numRows;
			
			// generate items of each catalog
			// @return array -> of all items in each catalog
			$entries = $this->generateCatalog($objCatalog, true, $this->catalog_template, $arrCatalogFields[$catid]);
			$arrEntries[$catid] = $entries;
			
			// kick out alias field if not in visibles list
			if(!in_array($this->strAliasField, $arrCatalogFields[$catid]))
				foreach($entries as $index => $entry)
					unset($arrEntries[$catid][$index]['data'][$this->strAliasField]);
				
			
			// amount
			foreach($entries as $index => $entry)
			{
				$id = $entry['id'];
				$amount = $arrAmount[$catid][$id];
				$arrEntries[$catid][$index]['amount'] = $amount;
				$totalAmount += $amount;
			}
					
			// Overrite image settings
			if($this->catalognotelist_thumbnails_override)
			{
				// seperate colName from checkbox value
				$arrImageFields = $this->getColNameFromArray(deserialize($this->catalognotelist_imagemain_field));
				
				foreach($entries as $index => $entry)
				{
					foreach($arrImageFields as $imageField)
					{
						if(array_key_exists($imageField, $entry['data']) && strlen($entry['data'][$imageField]['raw']))
						{
							$raw = $entry['data'][$imageField]['raw'];
							$size = deserialize($this->catalognotelist_imagemain_size);
							$lightbox = $this->catalognotelist_imagemain_fullsize;
							$src = $this->getImage($raw, $size[0], $size[1], $size[2]);
							
							// modify value string with new data
							$value = $entry['data'][$imageField]['value'];
							
							// replace src
							$value = preg_replace('/<img.*src="(.*?)"/', '<img src="'.$src.'" ', $value); 
							// replace size
							$value = preg_replace('/width="(.*?)" height="(.*?)"/', 'width="'.$size[0].'" height="'.$size[1].'" ', $value);
							
							if($lightbox)
							{
								$value = preg_replace('/rel="(.*?)"/', 'rel="lightbox"', $value);
							}
							else
							{
								/*preg_replace( '/<img.*src="(.*?)".*?>/', '<a href="\1">Image file</a>', $str );*/
								$value = preg_replace('/<a.*rel="(.*?)".*?>/', '', $value);
								$value = str_replace('</a>','', $value);
							}
							
							// update meta
							$entry['data'][$imageField]['meta'][0]['src'] = $src;
							$entry['data'][$imageField]['meta'][0]['w'] = $size[0];
							$entry['data'][$imageField]['meta'][0]['h'] = $size[1];
							$entry['data'][$imageField]['meta'][0]['wh'] = 'width="' .$size[0]. '" height="' .$size[1]. '"';
							
							// update entries array
							$arrEntries[$catid][$index]['data'][$imageField]['value'] = $value;
							
						}
					}
				}
			}
			
			
			
		} // endforeach
		
					
			
			
					
/**
 * work with Christian Schifflers FormCatalogNotelist
 * to create a form for each notelist item
 * => didn`t work out well, I couldn't get it to update the page
 */
		//$objFormCatalogNoteList = new FormCatalogNoteList(false); 
		//$objFormCatalogNoteList->catalog_visible = $arrCatalogFields[$catid];
		//$objFormCatalogNoteList->catalog = $catid;
		//$objFormCatalogNoteList->strTable = $this->strTable;
		//$objFormCatalogNoteList->strId = $this->id;
		//$objFormCatalogNoteList->id = $this->id;
		//$strFormTemplate = $objFormCatalogNoteList->calculateValue(false);
		//$index++;
		//$objTemplate->formnotelist .= $strFormTemplate;
		
		// generate template
		$objTemplate = new FrontendTemplate($this->catalog_template);
		$objTemplate->entries = $arrEntries;
		
		// catalog template vars
		$this->Template->total = $total;
			
		// catalog layout template vars
		$objTemplate->totalAmount = $totalAmount;
		$objTemplate->catalogCount = count($arrNotelist);
		$objTemplate->entriesCount = $total;
		
		// form vars
		$objTemplate->totalItems = $total;
		$objTemplate->totalAmount = $totalAmount;
		$objTemplate->action = $this->Environment->request;
		$objTemplate->label_amount = $GLOBALS['TL_LANG']['MSC']['catalognotelist_cataloglist']['label_amount'];
		$objTemplate->updateAmount = $GLOBALS['TL_LANG']['MSC']['catalognotelist_cataloglist']['updateAmount'];
		$objTemplate->remove = $GLOBALS['TL_LANG']['MSC']['catalognotelist_cataloglist']['remove'];
		
		// parse template
		$this->Template->catalog = $objTemplate->parse();
										
	}

	
	/**
	 * Substrings the values of the catalog visibles list to get the colName value and returns them as an array
	 * @return @array
	 */
	public function getColNameFromArray($array)
	{
		$arrReturn = array();
		foreach($array as $value)
		{
			$value = str_replace(' ', '', $value);
			$begin = strpos($value, '"')+1;
			$end = strrpos($value, '"');
			$return = substr($value, $begin, $end - $begin);
			$arrReturn[] = $return;
		}
		return $arrReturn;
	}

	public function getNameFromArray($array)
	{
		$arrReturn = array();
		foreach($array as $value)
		{
			$value = str_replace(' ', '', $value);
			$begin = 0;
			$end = strpos($value, ':' , 1);
			$return = substr($value, $begin, $end - $begin);
			$arrReturn[] = $return;
		}
		return $arrReturn;
	}


}






?>