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


class CatalogListNotelistHelper extends Frontend
{
	/**
	 * Update item amount or remove item from notelist
	 * called from: generatePage HOOK
	 * @param object
	 * @param object
	 * @param object
	 * @return void
	 */
	public function updateNotelist(Database_Result $objPage, Database_Result $objLayout, PageRegular $objPageRegular)
	{
		if(strpos($this->Input->post('FORM_SUBMIT'), 'catalognotelist') && strlen($this->Input->post('CATALOG')) && strlen($this->Input->post('CATALOG_ITEM')) )
		{
			$catid = $this->Input->post('CATALOG');
			$itemid = $this->Input->post('CATALOG_ITEM');
			$amount = $this->Input->post('AMOUNT_NOTELIST_ITEM');
			
			$this->import('Session');
			$arrSession = $this->Session->getData();
				
			if(!count($arrSession['catalog_notelist'])) 
			{
				return;
			}
			
			// Update amount
			if(isset($_REQUEST['UPDATE_NOTELIST_ITEM']))
			{
				foreach($arrSession['catalog_notelist'][$catid] as $index => $entry)
				{
					if($entry['id'] == $itemid)
					{
						$arrSession['catalog_notelist'][$catid][$index]['amount'] = $amount;
					}
				}
			}
			
			// remove item
			if(isset($_REQUEST['REMOVE_NOTELIST_ITEM']))
			{
				foreach($arrSession['catalog_notelist'][$catid] as $index => $entry)
				{
					if($entry['id'] == $itemid)
					{
						unset($arrSession['catalog_notelist'][$catid][$index]);
					}
				}
			}
			
			// update session and 
			$this->Session->setData($arrSession);
			
			// clear
			$this->Input->setPost('CATALOG', '');
			$this->Input->setPost('CATALOG_ITEM', '');
			
			// reload
			$this->reload();
		}
	}
}

?>