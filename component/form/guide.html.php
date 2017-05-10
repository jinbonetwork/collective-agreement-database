		<div class="radio-button"><label for="cat-example"><span><?php print $guide_subject; ?></span><i class="fa fa-angle-down"></i></label></div>
		<div class="guide-sub-category">
			<ul id="guide-<?php print $cid; ?>" data-cid="<?php print $cid; ?>" class="guide-items">
				<li id="guide-search" class="guide-chapter">
					<div class="guide-item-box">
						<input type="text" id="guide-search"><button type="button">검색</button>
					</div>
					<ul id="guide-search-sub-item" class="guide-sub-items">
					</ul>
				</li>
<?php foreach( $guide_taxonomy_terms[$cid] as $g_terms ) {
		if( !$g_terms['parent'] ) {?>
				<li id="guide-chapter-<?php print $g_terms['tid']; ?>" class="guide-chapter" data-tid="<?php print $g_terms['tid']; ?>">
					<div class="guide-item-box">
						<label for="guide-parent-<?php print $g_terms['tid']; ?>" data-tid="<?php print $g_terms['tid']; ?>" data-vid="<?php print $g_terms['vid']; ?>" data-name="<?php print ($g_terms['term_name'] ? $g_terms['term_name'] : $g_terms['name']); ?>" class="guide-chapter-label <?php print ($g_terms['nsubs'] ? "has-sub-category" : "guide-item-label"); ?>"><?php print $g_terms['name']; ?></label>
					</div>
<?php		if( $g_terms['nsubs'] ) {?>
					<ul id="guide-sub-item-<?php print $g_terms['tid']; ?>" class="guide-sub-items" data-parent="<?php print $g_terms['tid']; ?>">
<?php			foreach( $guide_taxonomy_terms[$cid] as $terms ) {
					if($terms['parent'] == $g_terms['tid']) {?>
						<li id="guide-sub-item-<?php print $terms['tid']; ?>" class="guide-sub-item" data-parent="<?php print $terms['parent']; ?>" data-tid="<?php print $terms['tid']; ?>">
							<div class="guide-item-box">
								<label class="guide-sub-item-label guide-item-label" data-tid="<?php print $terms['tid']; ?>" data-vid="<?php print $terms['vid']; ?>" data-name="<?php print ($terms['term_name'] ? $terms['term_name'] : $terms['name']); ?>"><?php print $terms['name']; ?></label>
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
