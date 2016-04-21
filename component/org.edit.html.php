<?php   
	\CADB\Lib\importResource('app-org-edit');
?>
		<form id="org-edit-form">
			<input type="hidden" name="oid" value="<?php print $organize['oid']; ?>" />
			<input type="hidden" name="vid" value="<?php print $organize['vid']; ?>" />
			<div class="fields-wrapper">
				<fieldset class="fields-org org-name">
					<input type="hidden" name="p1" id="p1" value="<?php print $organize['p1']; ?>" />
					<label class="field" for="nojo">노조명</label>
<?php			if($level <= 1) {?>
					<input type="text" name="nojo" id="nojo" value="<?php print $organize['nojo']; ?>" />
					<button type="button" data-for="nojo" data-depth="1">선택하기</button>
<?php			} else {?>
					<input type="hidden" name="nojo" id="nojo" value="<?php print $organize['nojo']; ?>" />
<?php				print $organize['nojo']; ?>
<?php			}?>
				</fieldset>
				<fieldset class="fields-org org-name">
					<input type="hidden" name="p2" id="p2" value="<?php print $organize['p2']; ?>" />
					<label class="field" for="sub1">본부명</label>
<?php			if($level <= 2) {?>
					<input type="text" name="sub1" id="sub1" value="<?php print $organize['sub1']; ?>" />
					<button type="button" data-for="sub1" data-depth="2">선택하기</button>
<?php			} else {?>
					<input type="hidden" name="sub1" id="sub1" value="<?php print $organize['sub1']; ?>" />
<?php				print $organize['sub1'];
				}?>
				</fieldset>
				<fieldset class="fields-org org-name">
					<input type="hidden" name="p3" id="p3" value="<?php print $organize['p3']; ?>" />
					<label class="field" for="sub2">지부명</label>
<?php			if($level <= 3) {?>
					<input type="text" name="sub2" id="sub2" value="<?php print $organize['sub2']; ?>" />
					<button type="button" data-for="sub2" data-depth="3">선택하기</button>
<?php			} else {?>
					<input type="hidden" name="sub2" id="sub2" value="<?php print $organize['sub2']; ?>" />
<?php				print $organize['sub2'];
				}?>
				</fieldset>
				<fieldset class="fields-org org-name">
					<input type="hidden" name="p4" id="p4" value="<?php print $organize['p4']; ?>" />
					<label class="field" for="sub2">지회명</label>
<?php			if($level <= 4) {?>
					<input type="text" name="sub3" id="sub3" value="<?php print $organize['sub3']; ?>" />
<?php			} else {?>
					<input type="hidden" name="sub3" id="sub3" value="<?php print $organize['sub3']; ?>" />
<?php				print $organize['sub3'];
				}?>
				</fieldset>
				<fieldset class="fields-org org-name">
					<label class="field" for="sub2">분회명</label>
<?php			if($level <= 5) {?>
					<input type="text" name="sub4" id="sub4" value="<?php print $organize['sub4']; ?>" />
<?php			} else {?>
					<input type="hidden" name="sub4" id="sub4" value="<?php print $organize['sub4']; ?>" />
<?php				print $organize['sub4'];
				}?>
				</fieldset>
<?php		foreach($fields as $fid => $field) {?>
				<fieldset class="fields-org org-field">
					<label class="field" for="f<?php print $fid; ?>"><?php print $field['subject']; ?></label>
					<div class="row">
<?php				switch($field['type']) {
						case 'taxonomy':
							if($field['multiple'])
								\CADB\View\Component::getComponent( 'form/taxonomy.multiple',array( 'fid'=>$fid, 'cid'=>$field['cid'], 'taxonomy_terms'=>$taxonomy_terms[$field['cid']], 'data'=>$organize['f'.$fid], 'required' => $field['required'] ) );
							else
								\CADB\View\Component::getComponent( 'form/taxonomy.single', array( 'fid'=>$fid, 'cid'=>$field['cid'], 'taxonomy_terms'=>$taxonomy_terms[$field['cid']], 'data'=>$organize['f'.$fid], 'required' => $field['required'] ) );
							break;
						case "date": ?>
							<input type="date" data-fid="<?php print $fid; ?>" data-required="<?php print $field['required']; ?>" class="cadb-field date" name="f<?php print $fid; ?>" value="<?php print $organize['f'.$fid]; ?>" />
<?php						break;
						case "int": ?>
							<input type="number" data-fid="<?php print $fid; ?>" data-required="<?php print $field['required']; ?>" class="cadb-field int" name="f<?php print $fid; ?>" value="<?php print $organize['f'.$fid]; ?>" />
<?php						break;
						default: ?>
							<input type="text" data-fid="<?php print $fid; ?>" data-required="<?php print $field['required']; ?>" class="cadb-field text" name="f<?php print $fid; ?>" value="<?php print $organize['f'.$fid]; ?>" />
<?php						break;
					}?>
					</div>
				</fieldset>
<?php		}?>
				<div class="label-divider"></div>
			</div>
			<fieldset class="buttons">
				<button type="button" name="modify">수정하기</button>
				<button type="button" name="delete">삭제하기</button>
				<button type="button" name="back">뒤로</button>
			</fieldset>
		</form>
