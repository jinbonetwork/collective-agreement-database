		<div class="<?php print $table; ?>-field-wrapper field-form-edit">
			<div class="<?php print $table; ?>-field-list field-list-wrap">
				<i class="fa fa-chain sortable"></i>
				<ol class="field-list sortable" data-table="<?php print $table; ?>">
<?php			foreach($fields as $fid => $field) {?>
					<li data-fid="<?php print $fid; ?>" data-idx="<?php print $field['idx']; ?>" class="field<?php print($field['iscolumn'] ? ' iscolumn' : '').($field['multiple'] ? ' multiple' : '').($field['required'] ? ' required' : '').' '.$field['type'].($field['active'] ? ' active' : '').($field['autocomplete'] ? ' autocomplete' : '').($field['system'] ? ' system' : ''); ?>" data-type="<?php print $field['type']; ?>" data-cid="<?php print $field['cid']; ?>" data-indextype="<?php print $field['indextype']; ?>">
						<div class="inner"><label><?php print $field['subject']; ?></label></div>
					</li>
<?php			}?>
				</ol>
			</div>
			<div class="field-form-item">
				<form class="field-form">
					<i class="fa fa-close"></i>
					<legend></legend>
					<input type="hidden" name="table" class="cadb-field-column" value="<?php print $table; ?>" tabindex="1" />
					<input type="hidden" id="fid" name="fid" class="cadb-field-column" value="" tabindex="2" />
					<input type="hidden" id="mode" name="mode" value="add" />
					<input type="hidden" id="idx" name="idx" value="" />
					<fieldset class="field-item">
						<label class="field-title" for="subject">제목</label>
						<input type="text" id="subject" name="subject" class="cadb-field-column" value="" tabindex="3" />
					</fieldset>
					<fieldset class="field-item">
						<label class="field-title" for="iscolumn">칼럼필드 여부</label>
						<div class="field-item-content new-form">
							<input type="checkbox" id="iscolumn" name="iscolumn" class="cadb-field-column" value="1" tabindex="4" /><label for="iscolumn">이 필드를 <?php print $table; ?> 테이블의 column으로 추가합니다.</label>
						</div>
						<div class="field-item-content modify-form">
							<?php print $table; ?> 의 column
						</div>
					</fieldset>
					<fieldset class="field-item">
						<label class="field-title" for="field-type">입력방식</label>
						<div class="field-item-content">
							<select id="field-type" name="type" class="cadb-field-column" tabindex="5" />
								<option value="char">문자열입력</option>
								<option value="text">장문</option>
								<option value="taxonomy">분류선택</option>
								<option value="int">정수입력</option>
								<option value="date">날짜입력</option>
							</select>
						</div>
					</fieldset>
					<fieldset class="field-item taxonomy">
						<label class="field-title" for="multiple">중복체크</label>
						<div class="field-item-content">
							<input type="checkbox" id="multiple" name="multiple" value="1" class="cadb-field-column" tabindex="6" /><label for="multiple">분류선택에 중복선택을 허용합니다.</label>
						</div>
					</fieldset>
					<fieldset class="field-item">
						<label class="field-title" for="required">필수입력</label>
						<div class="field-item-content">
							<input type="checkbox" id="required" name="required" value="1" class="cadb-field-column" tabindex="7" /><label for="required">이 필드는 필수입력 항목입니다.</label>
						</div>
					</fieldset>
					<fieldset class="field-item taxonomy">
						<label class="field-title" for="cid">분류</label>
						<div class="field-item-content">
							<select id="cid" name="cid" class="cadb-field-column" tabindex="8">
								<option value="0">분류선책</option>
<?php						foreach($taxonomy as $cid => $taxo) {?>
								<option value="<?php print $cid; ?>"><?php print $taxo['subject']; ?></option>
<?php						}?>
							</select>
							<button type="button" data-cid="cid" class="taxonomy-terms">세부항목관리</button>
							<div class="new-taxonomy-wrapper">
								<div class="new_taxonomy-button">
									<button type="button" class="taxonomy-add">새분류추가</button>
								</div>
								<div class="new-taxonomy-container">
									<input name="taxonomy_subject" type="text" class="text" />
									<button type="button" class="taxonomy-add-submit">추가</button>
									<button type="button" class="taxonomy-add-cancel">취소</button>
								</div>
							</div>
						</div>
					</fieldset>
					<fieldset class="field-item">
						<label class="field-title" for="active">활성화</label>
						<div class="field-item-content">
							<input type="checkbox" id="active" name="active" value="1" class="cadb-field-column" tabindex="9" /><label for="active">이 필드를 활성화시킵니다.</label>
						</div>
					</fieldset>
<?php			if($field['system']) {?>
					<fieldset class="field-item">
						<label class="field-title" for="system">시스템필드</label>
						<div class="field-item-content">
							이 필드는 시스템 고정 필드입니다.
						</div>
					</fieldset>
<?php			}
				$context = \CADB\Model\Context::instance();
				if($context->getProperty('service.redis')) {?>
					<fieldset class="field-item">
						<label class="field-title" for="autocomplete">자동완성</label>
						<div class="field-item-content">
							<input type="checkbox" id="autocomplete" name="autocomplete" value="1" class="cadb-field-column" tabindex="10" /><label for="autocomplete">이 필드를 자동완성 기능에 포함합니다.</label>
						</div>
					</fieldset>
<?php			}?>
					<fieldset class="field-item indextype">
						<label class="field-title" for="indextype">검색키사용</label>
						<div class="field-item-content">
							<select id="indextype" name="indextype" class="cadb-field-column" tabindex="11">
								<option value="none">사용안함</option>
								<option value="fulltext">사용</option>
							</select>
						</div>
					</fieldset>
					<fieldset class="field-button">
						<button type="submit" class="submit">입력</button>
						<button type="button" class="delete">삭제</button>
						<button type="button" class="close">닫기</button>
					</fieldset>
				</form>
			</div>
			<div class="taxonomy-terms-container">
				<div class="taxonomy-terms-list">
					<i class="fa fa-close"></i>
					<div class="taxonomy-terms-wrapper">
						<ol>
						</ol>
						<fieldset class="field-button">
							<button type="button" class="close">닫기</button>
						</fieldset>
					</div>
				</div>
			</div>
		</div>
