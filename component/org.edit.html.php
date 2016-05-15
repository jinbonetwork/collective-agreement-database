<?php   
	\CADB\Lib\importResource('app-org-edit');
?>
		<form id="org-edit-form">
			<input type="hidden" name="oid" value="<?php print $organize['oid']; ?>" />
			<input type="hidden" name="vid" value="<?php print $organize['vid']; ?>" />
			<div class="fields-wrapper">
				<fieldset class="fields-org org-name">
					<input type="hidden" name="p1" id="p1" class="org-parent" value="<?php print $organize['p1']; ?>" />
					<label class="field" for="nojo">노조명</label>
<?php			if($level <= 1) {?>
					<div class="org-name-form collapsed">
						<div class="org-input-form">
							<input type="text" name="nojo" id="nojo" class="org-name" data-depth="1" value="<?php print $organize['nojo']; ?>" />
						</div>
						<div class="org-search-form">
							<ul data-target="p1">
							</ul>
						</div>
						<button type="button" data-for="nojo" data-depth="1" class="search-orgs">선택하기</button>
					</div>
<?php			} else {?>
					<input type="hidden" name="nojo" id="nojo" class="org-name" value="<?php print $organize['nojo']; ?>" />
<?php				print $organize['nojo']; ?>
<?php			}?>
				</fieldset>
				<fieldset class="fields-org org-name">
					<input type="hidden" name="p2" id="p2" class="org-parent" value="<?php print $organize['p2']; ?>" />
					<label class="field" for="sub1">본부명</label>
<?php			if($level <= 2) {?>
					<div class="org-name-form collapsed">
						<div class="org-input-form">
							<input type="text" name="sub1" id="sub1" class="org-name" data-depth="2" value="<?php print $organize['sub1']; ?>" />
						</div>
						<div class="org-search-form">
							<ul data-target="p2">
							</ul>
						</div>
						<button type="button" data-for="sub1" data-depth="2" class="search-orgs">선택하기</button>
					</div>
<?php			} else {?>
					<input type="hidden" name="sub1" id="sub1" class="org-name" value="<?php print $organize['sub1']; ?>" />
<?php				print $organize['sub1'];
				}?>
				</fieldset>
				<fieldset class="fields-org org-name">
					<input type="hidden" name="p3" id="p3" class="org-parent" value="<?php print $organize['p3']; ?>" />
					<label class="field" for="sub2">지부명</label>
<?php			if($level <= 3) {?>
					<div class="org-name-form collapsed">
						<div class="fields-input-form">
							<input type="text" name="sub2" id="sub2" class="org-name" data-depth=3" value="<?php print $organize['sub2']; ?>" />
						</div>
						<div class="org-search-form">
							<ul data-target="p3">
							</ul>
						</div>
						<button type="button" data-for="sub2" data-depth="3" class="search-orgs">선택하기</button>
					</div>
<?php			} else {?>
					<input type="hidden" name="sub2" id="sub2" class="org-name" value="<?php print $organize['sub2']; ?>" />
<?php				print $organize['sub2'];
				}?>
				</fieldset>
				<fieldset class="fields-org org-name">
					<input type="hidden" name="p4" id="p4" class="org-parent" value="<?php print $organize['p4']; ?>" />
					<label class="field" for="sub2">지회명</label>
<?php			if($level <= 4) {?>
					<div class="org-name-form collapsed">
						<div class="fields-input-form">
							<input type="text" name="sub3" id="sub3" class="org-name" data-depth=4" value="<?php print $organize['sub3']; ?>" />
						</div>
						<div class="org-search-form">
							<ul data-target="p4">
							</ul>
						</div>
						<button type="button" data-for="sub3" data-depth="4" class="search-orgs">선택하기</button>
					</div>
<?php			} else {?>
					<input type="hidden" name="sub3" id="sub3" class="org-name" value="<?php print $organize['sub3']; ?>" />
<?php				print $organize['sub3'];
				}?>
				</fieldset>
				<fieldset class="fields-org org-name">
					<label class="field" for="sub2">분회명</label>
<?php			if($level <= 5) {?>
					<div class="org-name-form collapsed">
						<div class="fields-input-form">
							<input type="text" name="sub4" id="sub4" class="org-name" data-depth="5" value="<?php print $organize['sub4']; ?>" />
						</div>
						<div class="org-search-form">
							<ul data-target="">
							</ul>
						</div>
						<button type="button" data-for="sub4" data-depth="5" class="search-orgs">선택하기</button>
					</div>
<?php			} else {?>
					<input type="hidden" name="sub4" id="sub4" class="org-name" value="<?php print $organize['sub4']; ?>" />
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
				<button type="button" class="modify"><?php print ($organize['oid'] ? '수정하기' : '추가하기'); ?></button>
				<button type="button" class="delete<?php print ($organize['oid'] ? ' show' : ''); ?>">삭제하기</button>
				<button type="button" class="back">뒤로</button>
			</fieldset>
		</form>
