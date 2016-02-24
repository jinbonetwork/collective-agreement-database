<?php
$data = array(
	'fields'=>array(
		'org'=>$fields
	),
	'result'=>array(
		'total_cnt'=>$total_cnt,
		'total_page'=>$total_page,
		'page'=>$page,
		'count'=>@count($organize)
	),
	'orgs'=>$organize
);
echo json_encode($data);
?>
