<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,user-scalable=0,initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
	<title>전국공공운수노조 단체협약 데이터베이스</title>
<?php   print $this->header(); ?>

</head>
<body class="<?php print $breadcrumbs_class; ?>">
	<div class="container">
		<div class="header">
			 <div class="logos">
				<div class="organization">
					<div class="logo logo-org"></div>
				</div>
				<div class="title">
					<a href="<?php print url("/"); ?>"><div class="logo logo-DB"></div><span>단체협약 DB</span></a>
				</div>
			</div>
			<div class="anchors">
<?php		if(user_logged_in()) {?>
				<div class="logged-in<?php print(!user_logged_in() ? " hidden" : ""); ?>">
					<a href="" class="organization-info">내 조직</a>
					<a href="<?php print url('login/logout'); ?>" class="logged-out">로그아웃</a>
				</div>
<?php		} else {?>
				<div class="logged-out<?php print(user_logged_in() ? " hidden" : ""); ?>">
					<a href="<?php print url("login"); ?>" class="logged-in">로그인</a>
				</div>
<?php		}?>
			</div>
		</div>
		<div class="inner-container">
<?php		print $content; ?>
			<div class="side">
			</div>
			<div class="footer">
				<div class="banner">
					<div class="logo logo-DB-project-banner"></div>
				</div>
				<div class="labels">
					<p class="jinbonet">Powered by <a href="http://jinbo.net">진보넷</a></p>
					<p class="db-project">단체협약 DB 프로젝트 <a href="https://github.com/jinbonetwork/collective-agreement-database"><i class="fa fa-github-alt"></i></a></p>
				</div>
			</div>
		</div>
	</div>
	<?php print $this->footer(); ?>
</body>
</html>
