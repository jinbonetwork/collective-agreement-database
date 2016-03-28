		<div class="organize-field" data-fid="<?php print $fid; ?>" data-required="<?php print $required; ?>">
			<ul class="cadb-field organize-list" data-fid="<?php print $fid; ?>" data-required="<?php print $required; ?>">
<?php	if( is_array($orgs) ) {
			foreach($orgs as $org) {?>
				<li class="organize<?php print ($org['owner'] ? " is-owner" : ""); ?>" data-oid="<?php print $org['oid']; ?>" data-vid="<?php print ($org['vid'] ? $org['vid'] : $org['oid']); ?>" data-owner="<?php print $org['owner']; ?>" data-name="<?php print $org['name']; ?>"><input type="checkbox" data-oid="<?php print $org['oid']; ?>" value="1"<?php print ($org['owner'] ? " checked" : ""); ?> /><?php print $org['name']; ?><i class="delete fa fa-close"></i></li>
<?php 		}
		}?>
				<li class="add">
					<input type="text" id="new-f<?php print $fid; ?>" data-id="<?php print $fid; ?>">
					<i class="add fa fa-plus"></i>
					<i class="search fa fa-search-plus"></i>
				</li>
			</ul>
		</div>
