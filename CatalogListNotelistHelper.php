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


class CatalogListNotelistHelper extends Frontend
{
	public function parseFrontendTemplateHook($strContent, $strTemplate)
	{
		if(strpos($this->Input->post('FORM_SUBMIT'), 'catalognotelist') && strlen($_POST['FORM_SUBMIT']))
		{
			$catid = $this->Input->post('catid');
			$itemid = $this->Input->post('itemid');
			$amount = $this->Input->post('amount_'.$catid.'_'.$itemid);
			
			// UPDATE AMOUNT
			if(isset($_REQUEST['update_'.$catid.'_'.$itemid]))
			{
				$this->import('Session');
				$arrSession = $this->Session->getData();
				
				if(!count($arrSession['catalog_notelist'])) return $strContent;
				
				foreach($arrSession['catalog_notelist'][$catid] as $index => $entry)
				{
					if($entry['id'] == $itemid)
					{
						$arrSession['catalog_notelist'][$catid][$index]['amount'] = $amount;
					}
				}
			}
			
			// REMOVE ITEM
			if(isset($_REQUEST['remove_'.$catid.'_'.$itemid]))
			{
				$this->import('Session');
				$arrSession = $this->Session->getData();
				
				if(!count($arrSession['catalog_notelist'])) return $strContent;
				
				foreach($arrSession['catalog_notelist'][$catid] as $index => $entry)
				{
					if($entry['id'] == $itemid)
					{
						unset($arrSession['catalog_notelist'][$catid][$index]);
					}
				}
			}
			
			
			// update session and clear form_submit
			$this->Session->setData($arrSession);
			unset($_POST['FORM_SUBMIT']);
		}
	
		return $strContent;
	}
}

?>