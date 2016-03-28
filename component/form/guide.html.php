		<div class="radio-button"><label for="cat-example"><span>2015 모범 단체협약안</span><i class="fa fa-angle-down"></i></label></div>
		<div class="guide-sub-category">
			<ul id="guide-<?php print $cid; ?>" data-cid="<?php print $cid; ?>" class="guide-items">
<?php foreach( $guide_taxonomy_terms[$cid] as $g_terms ) {
			if( !$g_terms['parent'] ) {?>
				<li id="guide-chapter-<?php print $g_terms['tid']; ?>" class="guide-chapter" data-tid="<?php print $g_terms['tid']; ?>">
					<div class="guide-item-box">
						<label for="guide-parent-<?php print $g_terms['tid']; ?>" data-tid="<?php print $g_terms['tid']; ?>" data-vid="<?php print $g_terms['vid']; ?>" data-name="<?php print $g_terms['name']; ?>" class="guide-chapter-label<?php print ($g_terms['nsubs'] ? " has-sub-category" : ""); ?>"><?php print $g_terms['name']; ?></label>
					</div>
<?php		if( $g_terms['nsubs'] ) {?>
					<ul id="guide-sub-item-<?php print $g_terms['tid']; ?>" class="guide-sub-items" data-parent="<?php print $g_terms['tid']; ?>">
<?php			foreach( $guide_taxonomy_terms[$cid] as $terms ) {
					if($terms['parent'] == $g_terms['tid']) {?>
						<li class="guide-sub-item" data-parent="<?php print $terms['parent']; ?>" data-tid="<?php print $terms['tid']; ?>">
							<div class="guide-item-box">
								<label class="guide-sub-item-label" data-tid="<?php print $terms['tid']; ?>" data-vid="<?php print $terms['vid']; ?>" data-name="<?php print $terms['name']; ?>"><?php print $terms['name']; ?></label>
							</div>
						</li>
<?php				}
				}?>
				</ul>
<?php		}?>
			</li>
<?php	}
	}?>
		</ul>
	</div>
