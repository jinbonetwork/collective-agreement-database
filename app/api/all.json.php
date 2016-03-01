<?php
$data = array(
	'fields'=>array(
		'standard'=>$fields['guide_clause'],
		'orgs'=>$fields['organize'],
		'articles'=>$fields['agreement']
	),
	'result'=>$result,
	'orgs'=>$organize,
	'standard'=>$standard,
	'articles'=>$articles
);
print json_encode($data);
?>
