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
print json_encode($data);
?>
