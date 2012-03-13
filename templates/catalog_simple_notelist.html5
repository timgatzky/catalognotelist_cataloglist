<?php
/**
 * Sample "catalognotelist cataloglist" template file
 */
?>
<?php #print_r($this->entries); ?>

<?php if (count($this->entries)): ?>

<?php foreach($this->entries as $catalog => $entries): ?>
<div class="layout_simple notelist catalog_<?php echo $catalog; ?> block">
<?php foreach ($entries as $entry): ?>
<div class="item<?php echo $entry['class'] ? ' '.$entry['class'] : ''; ?>">
	<div class="amount">Anzahl: <span class="counter"><?php echo $entry['amount']; ?></span></div>
	
	<?php foreach ($entry['data'] as $field=>$data): ?>
	<?php if (strlen($data['raw']) && !in_array($field, array('catalog_name','parentJumpTo'))): ?>
	<div class="field <?php echo $field; ?>">
		<div class="label"><?php echo $data['label']; ?></div>
		<div class="value"><?php echo $data['value']; ?></div>
	</div>
	<?php endif; ?>
	<?php endforeach; ?>
	
	<? /*** link to catalogreader page ***/ ?>
	<?php if ($entry['showLink'] && $entry['link']): ?>
	<div class="link"><?php echo $entry['link']; ?></div>
	<?php endif; ?>
	<?php if ($entry['linkEdit']): ?>
	<div class="linkEdit"><?php echo $entry['linkEdit']; ?></div>
	<?php endif; ?>
	
	<? /*** notelist form ***/ ?>
	<div class="ce_form formnotelist">
		<form method="post" enctype="application/x-www-form-urlencoded" id="form_catalognotelist_<?php echo $catalog.'_'.$entry['id']; ?>" action="<?php echo $this->action; ?>">	
		<input type="hidden" name="FORM_SUBMIT" value="form_catalognotelist_<?php echo $catalog.'_'.$entry['id']; ?>" />
		<input type="hidden" name="catid" value="<?php echo $catalog; ?>" />
		<input type="hidden" name="itemid" value="<?php echo $entry['id']; ?>" />
		<input type="hidden" name="totalitems" value="<?php echo $this->totalItems; ?>" />
		<input type="hidden" name="totalamount" value="<?php echo $this->totalAmount; ?>" />
		<div class="submit_container">
			<input type="text" class="submit amount" name="amount_<?php echo $catalog.'_'.$entry['id']; ?>" value="<?php echo $entry['amount']; ?>">
			<input type="submit" class="submit update" name="update_<?php echo $catalog.'_'.$entry['id']; ?>" value="<?php echo $this->updateAmount; ?>">		
			<input type="submit" class="submit remove" name="remove_<?php echo $catalog.'_'.$entry['id']; ?>" value="<?php echo $this->remove; ?>">	
		</div>
		</form>
	</div>
	
</div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>


<div class="notelist_summary">
<span class="total_amount">Gesamt: <span class="counter"><?php echo $this->totalAmount; ?></span></span>
<span class="catalog_count">Kataloge: <span class="counter"><?php echo $this->catalogCount; ?></span></span>
<span class="notelist_entries">Gemerkt: <span class="counter"><?php echo $this->entriesCount; ?></span></span>
</div>

<?php else: ?>
<p class="info">Your Notelist is empty</p>
<?php endif; ?>