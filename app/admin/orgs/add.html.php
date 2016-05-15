<div id="organize-editor-page">
	<div class="organ-info-container">
		<div class="organ-info">
			<div class="header">
				<div class="organ-name">단체 추가하기</div>
			</div>
			<div class="content">
<?php			\CADB\View\Component::getComponent( 'org.edit',array( 'level'=>1, 'fields'=>$fields, 'organize'=>array(), 'taxonomy_terms'=>$taxonomy_terms ) ); ?>
			</div>
		</div>
	</div>
</div>
