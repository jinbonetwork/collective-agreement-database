<hr />
<div>
	<table border="0" cellpadding="2" cellspacing="1" width="100%">
		<tbody>
<?php if(is_array($this->fields['field'])) {
		foreach($this->fields['field'] as $f) {
			if($f['table'] != 'agreement') continue;
			if(!$this->articles['f'.$f['fid']]) continue;
			if($f['fid'] == 30 && $remove_30) continue; ?>
			<tr>
				<th style="vertical-align: top; padding-right:15px;" width="120"><?php print $f['subject']; ?></th>
				<td style="vertical-align: top;">
<?php			if(is_array($this->articles['f'.$f['fid']])) {
					switch($f['subject']) {
						case '교섭형태':
							$c = 0;
							foreach($this->articles['f'.$f['fid']] as $fv) {
								if($fv) {
									if($fv['name'] == '공동교섭') {
										$remove_30 = 1;
									}
									if(!$c) {
										print $fv['name'];
										$c++;
									} else {
										if($c == 1) print " (";
										else if($c > 1) ",";
										print $fv['name'];
										$c++;
									}
								}
							}
							if($c > 1) {
								print ")";
							}
							break;
						default:
							$c=0;
							foreach($this->articles['f'.$f['fid']] as $fv) {
								if($fv) print ($c++ ? "<br>" : "").$fv['name'];
							}
							break;
					}
				} else {
					print $this->articles['f'.$f['fid']];
				}?>
				</td>
			</tr>
<?php	}
	}?>
		</tbody>
	</table>
	<br /><hr />
</div>
