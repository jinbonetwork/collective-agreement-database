<?php
	\CADB\Lib\importResource('app-member-gnu5-edit');
?>
		<form id="member-edit-form">
			<input type="hidden" name="mode" value="<?php print $mode; ?>" />
			<input type="hidden" name="mb_no" value="<?php print $member['mb_no']; ?>" />
			<div class="fields-wrapper">
				<fieldset class="fields-member">
					<label class="field" for="mb_id">아이디</label>
					<div class="member-form">
						<input type="text" id="mb_id" name="mb_id" value="<?php print $member['mb_id']; ?>" />
					</div>
				</fieldset>
				<fieldset class="fields-member">
					<label class="field" for="mb_password">비밀번호</label>
					<div class="member-form">
						<input type="password" id="mb_password" name="mb_password" value="" />
					</div>
				</fieldset>
				<fieldset class="fields-member">
					<label class="field" for="mb_password_confirm">비밀번호 확인</label>
					<div class="member-form">
						<input type="password" id="mb_password_confirm" name="mb_password_confirm" value="" />
					</div>
				</fieldset>
				<fieldset class="fields-member">
					<label class="field" for="mb_name">이름</label>
					<div class="member-form">
						<input type="text" id="mb_name" name="mb_name" value="<?php print $member['mb_id']; ?>" />
					</div>
				</fieldset>
				<fieldset class="fields-member">
					<label class="field" for="mb_nick">닉네임</label>
					<div class="member-form">
						<input type="text" id="mb_nick" name="mb_nick" value="<?php print $member['mb_nick']; ?>" />
					</div>
				</fieldset>
				<fieldset class="fields-member">
					<label class="field" for="mb_email">이메일</label>
					<div class="member-form">
						<input type="text" id="mb_email" name="mb_email" value="<?php print $member['mb_email']; ?>" />
					</div>
				</fieldset>
				<fieldset class="fields-member">
					<label class="field" for="mb_level">등급</label>
					<div class="member-form">
						<select id="mb_level" name="mb_level">
							<option value="2"<?php print ($member['mb_level'] == 2 ? ' selected' : ''); ?>>이용자</option>
							<option value="6"<?php print ($member['mb_level'] == 6 ? ' selected' : ''); ?>>담당자</option>
							<option value="10"<?php print ($member['mb_level'] == 10 ? ' selected' : ''); ?>>운영자</option>
						</select>
					</div>
				</fieldset>
				<fieldset class="fields-member org-roles<?php print ((!$member['roles'] || @count($member['roles']) < 1) ? ' collapsed' : ''); ?>">
					<label class="field" for="mb_level">책임단체</label>
					<div class="member-form">
						<div class="organize-field">
							<ul class="organize-list">
<?php					if(is_array($member['roles'])) {
							foreach($member['roles'] as $oid => $role) {?>
								<li class="organize" data-oid="<?php print $oid; ?>" data-role="<?php print $role['role']; ?>">
									<?php print $role['fullname'] ;?>
									<i class="delete fa fa-close"></i>
								</li>
<?php						}
						}?>
								<li class="add">
									<i class="add fa fa-plus"></i>
								</li>
							</ul>
						</div>
					</div>
				</fieldset>
				<div class="label-divider"></div>
			</div>
			<fieldset class="buttons">
				<button type="button" class="modify"><?php print ($member['mb_no'] ? '수정하기' : '추가하기'); ?></button>
				<button type="button" class="delete<?php print ($member['mb_no'] ? ' show' : ''); ?>">삭제하기</button>
				<button type="button" class="back">뒤로</button>
			</fieldset>
		</form>
