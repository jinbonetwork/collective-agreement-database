<hr />
<div>
	<table border="0" cellpadding="2" cellspacing="1" width="100%">
		<tbody>
<?php if(is_array($this->fields['field'])) {
		foreach($this->fields['field'] as $f) {
			if($f['table'] != 'agreement') continue;
			if(!$this->articles['f'.$f['fid']]) continue; ?>
			<tr>
				<th style="vertical-align: top; padding-right:15px;" width="120"><?php print $f['subject']; ?></th>
				<td style="vertical-align: top;">
<?php			if(is_array($this->articles['f'.$f['fid']])) {
					$c=0;
					foreach($this->articles['f'.$f['fid']] as $fv) {
						if($fv) print ($c++ ? "<br>" : "").$fv['name'];
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
