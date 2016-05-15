<?php
	\CADB\Lib\importResource('app-article-edit');
?>
	<form id="article-edit-form">
		<input type="hidden" name="nid" value="<?php print $articles['nid']; ?>" />
		<input type="hidden" name="did" value="<?php print $articles['did']; ?>" />
		<fieldset class="form-header">
			<div class="form-header-fixed app-page">
				<fieldset class="fields-guide-group collapsed">
<?php			foreach($guide_taxonomy_terms as $cid => $g_taxonomy_terms) {
					\CADB\View\Component::getComponent('form/guide', array( 'cid'=>$cid, 'guide_taxonomy_terms' => $g_taxonomy_terms ) );
				}?>
				</fieldset>
				<fieldset class="fields-title">
					<legend><span>단체협약 <?php print ($articles['nid'] ? '수정하기' : '추가하기'); ?></span></legend>
				</fieldset>
			</div>
		</fieldset>
		<fieldset class="fields-group meta">
			<div class="inner-wrapper">
<?php		foreach($fields['field'] as $fid => $f) {
				if($f['table'] != 'agreement') continue; ?>
				<fieldset class="fields">
					<label class="field-label" for="f<?php print $fid; ?>"><?php print $f['subject']; ?></label>
					<div class="field-wrapper">
<?php			switch($f['type']) {
					case "taxonomy":
						if($f['multiple'])
							\CADB\View\Component::getComponent('form/taxonomy.multiple', array( 'fid'=>$fid, 'cid'=>$f['cid'], 'taxonomy_terms'=>$taxonomy_terms[$f['cid']], 'data'=>$articles['f'.$fid], 'required'=>$f['required'] ) );
						else
							\CADB\View\Component::getComponent('form/taxonomy.single', array( 'fid'=>$fid, 'cid'=>$f['cid'], 'taxonomy_terms'=>$taxonomy_terms[$f['cid']], 'data'=>$articles['f'.$fid], 'required'=>$f['required'] ) );
						break;
					case "organize":
						\CADB\View\Component::getComponent('form/organize',array( 'fid'=>$fid, 'orgs'=>$articles['f'.$fid], 'required'=>$f['required'] ) );
						break;
					case "date": ?>
						<input type="date" data-fid="<?php print $fid; ?>" data-required="<?php print $f['required']; ?>" class="cadb-field date" name="f<?php print $fid; ?>" value="<?php print $articles['f'.$fid]; ?>" />
<?php					break;
					case "int": ?>
						<input type="number" data-fid="<?php print $fid; ?>" data-required="<?php print $f['required']; ?>" class="cadb-field int" name="f<?php print $fid; ?>" value="<?php print $articles['f'.$fid]; ?>" />
<?php					break;
					default:
						break;
				}?>
					</div>
				</fieldset>
<?php		}?>
			<fieldset class="buttons">
				<button type="submit"><?php print ($articles['nid'] ? "수정하기" : "추가하기"); ?></button>
				<button type="button" class="article-fork<?php print ($articles['nid'] ? ' show' : ''); ?>">복사하기</button>
				<button type="button" class="article-delete<?php print ($articles['nid'] ? ' show' : ''); ?>">삭제하기</button>
			</fieldset>
			</div>
		</fieldset>
		<fieldset class="fields-group content">
			<div class="inner-wrapper">
				<fieldset class="fields subject">
					<label class="field-label" for="subject">단협제목</label>
					<input type="text" id="subject" name="subject" value="<?php print $articles['subject']; ?>" />
				</fieldset>
				<fieldset id="edit-agreement">
					<div class="editor content"><?php print $articles['content']; ?></div>
				</fieldset>
			</div>
		</fieldset>
	</form>
