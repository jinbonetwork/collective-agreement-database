<div id="agreement-editor-page">
<?php 
	$args = array(
		'fields' => $fields,
		'taxonomy' => $taxonomy,
		'taxonomy_terms' => $taxonomy_terms,
		'guide_taxonomy_terms' => $guide_taxonomy_terms
	);
	\CADB\View\Component::getComponent('article.edit',$args); ?>
</div>
