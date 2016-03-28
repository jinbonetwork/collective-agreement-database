<?php $selected_tid = array(); ?>
	<div class="taxonomy-field multibple collapsed" data-fid="<?php print $fid; ?>" data-required="<?php print $required; ?>">
		<label for="taxonomy-f<?php $cid; ?>" class="select-box">
		<?php if( is_array($data) ) {?>
			<ul class="cadb-field taxonomy-list" data-fid="<?php print $fid; ?>" data-required="<?php print $required; ?>">
<?php		foreach($data as $v) {?>
				<li class="taxonomy" data-cid= "<?php print $cid; ?>" data-tid="<?php print $v['tid']; ?>" data-vid="<?php print $v['vid']; ?>" data-name="<?php print $v['name']; ?>">
					<?php print $v['name']; ?>
				</li>
<?php		}?>
			</ul>
<?php	} else {
			print "지정안됨";
		}?>
		</label>
		<ul id="taxonomy-f<?php $cid; ?>" class="taxonomy-items multiple">
<?php	foreach($taxonomy_terms as $terms) {
			if($terms['parent']) {
				if($current_parent != $terms['parent']) {
					$current_parent = $terms['parent'];
					$depth++;
				}
			}
			else $depth = 0;
			if( in_array($terms['tid'],$selected_tid) ) $checked = " selected";
			else $checked = ""; ?>
			<li class="taxonomy-item sub-<?php print $depth; ?>" data-cid=<?php print $cid; ?> data-parent="<?php print $terms['parent']; ?>" data-tid="<?php print $terms['tid']; ?>" data-name="<?php print $terms['name']; ?>" data-nsubs="<?php print $terms['nsubs']; ?>">
				<input type="checkbox" name="c<?php print $cid;?>[]" value="<?php print $terms['tid']; ?>"<?php print $checked; ?> /> <?php print $terms['name']; ?>
			</li>
<?php	}?>
		</ul>
	</div>
