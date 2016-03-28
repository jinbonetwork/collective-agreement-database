<?php $selected_tid = array(); ?>
	<div class="taxonomy-field single collapsed" data-fid="<?php print $fid; ?>" data-required="<?php print $required; ?>">
		<label for="taxonomy-<?php print $cid; ?>" class="select-box">
		<?php if( is_array($data) ) {?>
			<ul class="cadb-field taxonomy-list" data-fid="<?php print $fid; ?>" data-required="<?php print $required; ?>">
<?php		foreach($data as $v) {
				$selected_tid[] = $v['tid']; ?>
				<li class="taxonomy" data-cid= "<?php print $cid; ?>" data-tid="<?php print $v['tid']; ?>" data-vid="<?php print $v['vid']; ?>" data-name="<?php print $v['name']; ?>"><?php print $v['name']; ?></li>
<?php		}?>
			</ul>
<?php	} else {
			print "지정안됨";
		}?>
		</label>
		<ul id="taxonomy-<?php print $cid; ?>" data-cid="<?php print $cid; ?>" class="taxonomy-items single">
<?php	foreach($taxonomy_terms as $terms) {
			if($terms['parent']) {
				if($current_parent != $terms['parent']) {
					$current_parent = $terms['parent'];
					$depth++;
				}
			}
			else $depth = 0;
			if( in_array($terms['tid'],$selected_tid) ) $selected = " selected";
			else $selected = ""; ?>
			<li class="taxonomy-item sub-<?php print $depth; ?><?php print $selected; ?>" data-cid=<?php print $cid; ?> data-parent="<?php print $terms['parent']; ?>" data-tid="<?php print $terms['tid']; ?>" data-vid="<?php print $terms['vid']; ?>" data-name="<?php print $terms['name']; ?>" data-nsubs="<?php print $terms['nsubs']; ?>"><?php print $terms['name']; ?></li>
<?php	}?>
		</ul>
	</div>
