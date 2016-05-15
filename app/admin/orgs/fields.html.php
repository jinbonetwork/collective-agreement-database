		<h3>조직 필드 관리</h3>
		<div class="orgs-field-wrapper field-wrapper">
			<ul class="orgs-field-list field-list">
<?php		foreach($fields as $fid => $field) {?>
				<li data-fid="<?php print $fid; ?>" class="field<?php print($field['iscolumn'] ? ' iscolumn' : '').($field['required'] ? ' required' : '').' '.$field['type']; ?>">
					<a href=""><?php print $field['subject']; ?></a>
				</li>
<?php		}?>
			</ul>
		</div>
