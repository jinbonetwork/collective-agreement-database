<div id="login-container" class="<?php print $params['output']; ?>">
	<div class="wrap">
		<h2>로그인</h2>
		<form id="login-form" class="ui-form" name="login" action="<?php print \CADB\Lib\url("gnu5/bbs/login_check.php"); ?>" method="POST" onsubmit="return check_login(this);">
			<input type="hidden" name="url" value="<?php print ($params['requestURI'] ? $params['requestURI'] : "/"); ?>" />
			<fieldset class="ui-form-items login cadb">
				<div class="ui-form-item user-id">
					 <div class="ui-form-item-control">
					 	<input type="text" id="login_id" class="input text" name="mb_id" placeholder="아이디" />
					 </div>
				</div>
				<div class="ui-form-item password">
					 <div class="ui-form-item-control">
					 	<input type="password" id="login_pw" class="input text" name="mb_password" placeholder="비밀번호" />
					 </div>
				</div>
				<div class="ui-form-item alert">
				</div>
				<button type="submit" class="button submit">로그인</button>
				<div class="info">
					<p>비밀번호, 아이디 개설 문의는</p>
					<p>02-7443-2222 (정책기획국장)</p>
				</div>
			</fieldset>
		</form>
	</div>
</div>
