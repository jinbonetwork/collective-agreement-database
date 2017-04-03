			<h3>검색옵션 관리</h3>
			<div class="search-option-container">
<?php		foreach($search_option as $option) {?>
				<div class="search-option-box column<?php print $column_cnt; ?>">
				<?php print_r($option); ?>
				</div>
<?php		}?>
<?php			print_r($taxonomies);
?>
			</div>
