	<div class="administrator-container">
		<div class="top_menu">
			<h2><a href="<?php print \CADB\Lib\url("admin"); ?>">단체협약DB 관리화면</a></h2>
			<ul class="admin-menu">
				<li class="organize"><a href="<?php print \CADB\Lib\url("admin/orgs"); ?>">조직관리</a></li>
				<li class="guide"><a href="<?php print \CADB\Lib\url("admin/standards"); ?>">모범단협관리</a></li>
				<li class="agreement"><a href="<?php print \CADB\Lib\url("admin/articles"); ?>">단체협약관리</a></li>
				<li class="member"><a href="<?php print \CADB\Lib\url("admin/member"); ?>">회원관리</a></li>
				<li class="gnu5"><a href="<?php print \CADB\Lib\url("gnu5/adm"); ?>" target="_blank">GNU5</a></li>
			</ul>
		</div>
		<div class="administrator-content<?php print ($fullscreen ? ' fullscreen' : ''); ?>">
<?php		print $content; ?>
		</div>
	</div>
