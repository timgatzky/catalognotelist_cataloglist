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
 * @copyright  Tim Gatzky 2012
 * @author     Tim Gatzky <info@tim-gatzky.de>
 * @package    catalognotelist_cataloglist 
 * @license    LGPL 
 * @filesource
 */


class ModuleCatalogListNotelist extends ModuleCatalogList
{
	/**
	 * @var
	 */
	protected $totalAll = 0;
	protected $countCatalogs = 0;
	protected $countEntries = 0;
	
	/**
	 * Template
	 */
	protected $strTemplate = 'mod_cataloglist_notelist';
		
	
	/**
	 * Generate Module
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			
			$objTemplate->wildcard  = $GLOBALS['TL_LANG']['CATALOGLIST_NOTELIST']['WILDCARD'];
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			if (version_compare(VERSION.'.'.BUILD, '2.9.0', '>='))
			{
				$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
			}	
			else
			{
				$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;
			}
			
			return $objTemplate->parse();
		}
		
		
		// Handle multiple catalogs:
		$arrCatalogs = deserialize($this->catalognotelist_catalogs);
		$arrNotelist = $this->Session->get('catalog_notelist');
		
		// throw out catalogs selected but empty in notelist 
		$tmp = array();
		foreach($arrCatalogs as $i => $catalog)
		{
			if(count($arrNotelist[$catalog]))
			{
				$tmp[] = $catalog;
			}
		}
		$arrCatalogs = $tmp;
		unset($tmp);
		
		// nothing to show: return compiled
		if(!count($arrCatalogs))
		{
			$this->catalog = 0;
			return parent::generate();
		}
		
		// get visible fields
		$arrVisibles = $this->getVisibles();
		
		// get linked fields
		$arrIsLink = array();
		if($this->catalog_link_override)
		{
			$arrIsLink = $this->getLinkedFields();
		}
		
		// get image override fields
		$arrImages = array();
		if($this->catalog_thumbnails_override)
		{
			if($this->catalognotelist_imagemain_field)
			{
				$arrImages = $this->getImageFields();
			}
			if($this->catalognotelist_imagegallery_field)
			{
				$arrGallery = $this->getGalleryFields();
			}
		}
		
		// count all notelist entries
		foreach($arrNotelist as $c)
		{
			foreach($c as $e)
			{
				$this->countEntries += 1;
			}
		}
		
		$this->countCatalogs = count($arrCatalogs);
		
		// generate module for each catalog selected
		for($i=0; $i < count($arrCatalogs); $i++)
		{
			$this->catalog = $arrCatalogs[$i];
			$this->catalog_visible = $arrVisibles[$this->catalog];
			$this->catalog_islink = $arrIsLink[$this->catalog];
			$this->catalog_imagemain_field = $arrImages[$this->catalog];
			$this->catalog_imagegallery_field = $arrGallery[$this->catalog];
			$this->total = $this->countEntries;
			
			if($i > 0)
			{
				$this->headline = '';
				$this->total = '';
			}
									
			// Append summary at the end
			if($i == count($arrCatalogs)-1)
			{
				$this->summary = true;
			}
			
			// generate
			$strBuffer .= parent::generate();
		}
		
		return $strBuffer;
	}
	
		
	/**
	 * Generate module
	 */
	protected function compile()
	{
		$arrNotelist = $this->Session->get('catalog_notelist');
		
		// notelist is empty
		if( !count($arrNotelist) || !$this->catalog )
		{
			$objTemplate = new FrontendTemplate($this->catalog_template);
			$objTemplate->empty = $GLOBALS['TL_LANG']['MSC']['CATALOGNOTELIST_CATALOGLIST']['empty'];
			$objTemplate->entries = array();
			$this->Template->catalog = $objTemplate->parse();
			
			return;
		} 
		
		// create WHERE clause for sql query
		$strWhere = '';
		
		$arrItems = array();
		foreach($arrNotelist[$this->catalog] as $entry)
		{
			$arrItems[] = $entry['id'];
		}
		
		$strWhere = 'id IN('.implode(',', $arrItems).')';
				
		// fetch catalog items
		$objCatalog = $this->fetchCatalogItems($this->catalog_visible,$strWhere);
		
		if(!$objCatalog->numRows)
		{
			return;
		}
		
		// generate catalog
		$arrCatalog = $this->generateCatalog($objCatalog, true, $this->catalog_visible, true);
		
		// overwrite single image fields
		if($this->catalog_thumbnails_override && count($this->catalog_imagemain_field) > 0)
		{
			$arrCatalog = $this->getSingleImageThumbnails($arrCatalog);
		}
		
		// overwrite gallery fields
		if($this->catalog_thumbnails_override && count($this->catalog_imagegallery_field) > 0)
		{
			$arrCatalog = $this->getGalleryThumbnails($arrCatalog);
		}
		
		// add current amount for each item
		foreach($arrCatalog as $i => $entry)
		{
			foreach($arrNotelist[$this->catalog] as $notelistEntry)
			{
				if($entry['id'] == $notelistEntry['id'])
				{
					$arrCatalog[$i]['amount'] = $notelistEntry['amount'];
					
					// sum up amount
					$this->totalAll += $notelistEntry['amount'];
					
					// count entries
					$this->totalEntries += 1;
				}
			}
		}
					
		
		// Template vars
		$this->Template->catalogId = $this->catalog;
		#$this->Template->total = $this->totalEntries;
		
		$this->Template->labelTotal = $GLOBALS['TL_LANG']['MSC']['CATALOGNOTELIST_CATALOGLIST']['label_total'];
		$this->Template->labelCatalogs = $GLOBALS['TL_LANG']['MSC']['CATALOGNOTELIST_CATALOGLIST']['label_catalogs'];
		$this->Template->labelEntries = $GLOBALS['TL_LANG']['MSC']['CATALOGNOTELIST_CATALOGLIST']['label_entries'];
		
		$this->Template->totalAll = $this->totalAll;
		$this->Template->countCatalogs = $this->countCatalogs;
		$this->Template->countEntries = $this->totalEntries;
		
		// catalog entries template
		$objTemplate = new FrontendTemplate($this->catalog_template);
		$objTemplate->entries = $arrCatalog;
		
		// form vars
		$objTemplate->totalItems = $total;
		$objTemplate->totalAmount = $totalAmount;
		$objTemplate->action = $this->Environment->request;
		$objTemplate->update = $GLOBALS['TL_LANG']['MSC']['CATALOGNOTELIST_CATALOGLIST']['submit_update'];
		$objTemplate->remove = $GLOBALS['TL_LANG']['MSC']['CATALOGNOTELIST_CATALOGLIST']['submit_remove'];
		
		// parse template
		$this->Template->catalog = $objTemplate->parse();
							
	}
	
	
	/**
	 * Overwrite single image fields and return catalog array
	 * @param array
	 * @return array
	 */
	protected function getSingleImageThumbnails($arrCatalog)
	{
		$size = deserialize($this->catalog_imagemain_size);
		
		foreach($arrCatalog as $i => $catalog)
		{
			$url = $catalog['url'];
			foreach($this->catalog_imagemain_field as $field)
			{
				// skip if field is not visible
				if(!in_array($field, $this->catalog_visible))
				{
					continue;
				}
				
				$isLink = false;
				if(in_array($field, $this->catalog_islink) || $this->catalog_imagemain_fullsize)
				{
					$isLink = true;
				}
				
				$src = $catalog['data'][$field]['raw'];
				$image = $this->getImage($src, $size[0], $size[1], $size[2]);
				$imageHtml = $this->replaceInsertTags('{{image::'.$image.'}}');
				
				$arrClass = preg_match('/class="(.*?)\"/', $catalog['data'][$field]['value'],$result);
				$class = $result[1];
				
				$value = '';
				if($isLink)
				{
					$lb = '';
					$href = $url;
					//lightbox / fullscreen
					if($this->catalog_imagemain_fullsize)
					{
						$lb = 'data-lightbox="group:notelist_'.$catalog['pid'].'_'.$catalog['id'].'_single;"';
						$href = $src;
					}
					$value = sprintf('<a class="%s" href="%s" title="%s" %s >%s</a>',
						$class,
						$href,
						$href,
						$lb,
						$imageHtml
					);
				}
				else
				{
					$value = sprintf('<span class="%s">%s</span>',$class,$imageHtml);
				}
				
				// write catalog
				
				$arrCatalog[$i]['data'][$field]['value'] = $value; 
				$arrCatalog[$i]['data'][$field]['meta'][0]['src'] = $image;
				$arrCatalog[$i]['data'][$field]['meta'][0]['w'] = $size[0];
				$arrCatalog[$i]['data'][$field]['meta'][0]['h'] = $size[0];
				$arrCatalog[$i]['data'][$field]['meta'][0]['wh'] = 'width="'.$size[0].'" height="'.$size[1].'"';
			}
		}
		
		return $arrCatalog;
	}
	
	
	/**
	 * Overwrite gallery images and return catalog array
	 * @param array
	 * @return array
	 */
	protected function getGalleryThumbnails($arrCatalog)
	{
		$size = deserialize($this->catalog_imagegallery_size);
		
		foreach($arrCatalog as $i => $catalog)
		{
			$url = $catalog['url'];
			
			foreach($this->catalog_imagegallery_field as $field)
			{
				// skip if field is not visible
				if(!in_array($field, $this->catalog_visible))
				{
					continue;
				}
				
				$isLink = false;
				if(in_array($field, $this->catalog_islink) || $this->catalog_imagegallery_fullsize)
				{
					$isLink = true;
				}
				
				$arrSrc = deserialize($catalog['data'][$field]['files']);
				
				// field class
				$arrClass = preg_match('/class="(.*?)\"/', $catalog['data'][$field]['value'],$result);
				$class = $result[1];
				
				$arrImageValue = array();
				
				foreach($arrSrc as $imgIndex => $src)
				{
					$image = $this->getImage($src, $size[0], $size[1], $size[2]);
					$imageHtml = $this->replaceInsertTags('{{image::'.$src.'?width='.$size[0].'&height='.$size[1].'&crop='.$size[2].'}}');
					
					// classes
					$arrImageClass = array('image');
					if($imgIndex < 1) $arrImageClass[] = 'first';
					if($imgIndex >= count($arrSrc)-1) $arrImageClass[] = 'last';
					$imgIndex%2 == 0 ? $arrImageClass[] = ' even' : $arrImageClass[] = ' odd'; // even/odd
					
					if($isLink)
					{
						$lb = '';
						$href = $url;
						//lightbox / fullscreen
						if($this->catalog_imagegallery_fullsize)
						{
							$lb = 'data-lightbox="group:notelist_'.$catalog['pid'].'_'.$catalog['id'].'_multi;"';
							$href = $src;
						}
						$arrImageValue[] = sprintf('<a class="%s" href="%s" title="%s" %s >%s</a>',
							implode(' ',$arrImageClass),
							$href,
							$href,
							$lb,
							$imageHtml
						);
					}
					else
					{
						$arrImageValue[] = sprintf('<span class="%s">%s</span>',implode(' ',$arrImageClass),$imageHtml);
					}
					
					// write meta
					$arrCatalog[$i]['data'][$field]['meta'][$imgIndex]['src'] = $image;
					$arrCatalog[$i]['data'][$field]['meta'][$imgIndex]['w'] = $size[0];
					$arrCatalog[$i]['data'][$field]['meta'][$imgIndex]['h'] = $size[0];
					$arrCatalog[$i]['data'][$field]['meta'][$imgIndex]['wh'] = 'width="'.$size[0].'" height="'.$size[1].'"';
				}
				
				// write catalog
				$value = sprintf('<span class="%s">%s</span>',$class,implode('',$arrImageValue));
				
				$arrCatalog[$i]['data'][$field]['value'] = $value;
			}
		}
		
		return $arrCatalog;
	}
	
	/**
	 * Get catalog fields as an array listed by the catalog id
	 * @param array
	 * @return array
	 */
	protected function getCatalogConformFields($arrInput)
	{
		if(!isset($arrInput))
		{
			return array();
		}
		
		$arrReturn = array();
		// seperate visible fields and them list by catalog id
		foreach(deserialize($this->catalognotelist_catalogs) as $catalog)
		{
			foreach(deserialize($arrInput) as $visible)
			{
				$split = explode('_', $visible);
				$catId = $split[0];
				$fieldId = $split[1];
				$colName = $split[2];
				
				// handle column names with underscore
				$arrParts = array();
				if(count($split) > 3)
				{
					foreach($split as $i => $part)
					{
						if($i < 2) continue;
						
						$arrParts[] = $part;
					}
					$colName = implode('_', $arrParts);
				}
				
				if($catId == $catalog)
				{
					$arrReturn[$catalog][] = $colName;
				}				
			}
		}
		
		return $arrReturn;
	}
	
	/**
	 * Get visible catalog fields as an array listed by the catalog id
	 * @param array
	 * @return array
	 */
	protected function getVisibles()
	{
		return self::getCatalogConformFields($this->catalog_visible);
	}
	
	/**
	 * Get fields linked to the entry as an array listed by the catalog id
	 * @param array
	 * @return array
	 */
	protected function getLinkedFields()
	{
		return self::getCatalogConformFields($this->catalog_islink);
	}
	
	
	/**
	 * Get image fields as an array listed by the catalog id
	 * @param array
	 * @return array
	 */
	protected function getImageFields()
	{
		return self::getCatalogConformFields($this->catalognotelist_imagemain_field);
	}
	
	/**
	 * Get gallery fields as an array listed by the catalog id
	 * @param array
	 * @return array
	 */
	protected function getGalleryFields()
	{
		return self::getCatalogConformFields($this->catalognotelist_imagegallery_field);
	}
	
	
}






?>