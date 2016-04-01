<?php
if($fields) {
	$data = array(
		'fields'=>array(
			'standard'=>$fields
		)
	);
}
$data['result'] = $result;
$data['standard'] = $standard;
$data['indexes'] = $indexes;
print json_encode($data);
?>
