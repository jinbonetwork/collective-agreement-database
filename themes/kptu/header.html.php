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
					<a href="http://www.kptu.net" target="_blank"><div class="logo logo-org"></div></a>
				</div>
				<div class="title">
					<a href="<?php print \CADB\Lib\url("/"); ?>"><div class="logo logo-DB"></div><span>단체협약 DB</span></a>
				</div>
			</div>
			<div class="anchors">
<?php		if(\CADB\Lib\user_logged_in()) {?>
				<div class="logged-in<?php print(!\CADB\Lib\user_logged_in() ? " hidden" : ""); ?>">
<?php			if( \CADB\Lib\isMaster() ) {?>
					<a href="<?php print \CADB\Lib\url("admin"); ?>" class="organization-info">관리화면</a>
<?php			}?>
					<a href="<?php print \CADB\Lib\url('gnu5/bbs/board.php?bo_table=opinion'); ?>" class="organization-info">의견게시판</a>
					<a href="<?php print \CADB\Lib\url('login/logout'); ?>" class="logged-out">로그아웃</a>
				</div>
<?php		} else {?>
				<div class="logged-out<?php print(\CADB\Lib\user_logged_in() ? " hidden" : ""); ?>">
					<a href="<?php print \CADB\Lib\url("login"); ?>" class="logged-in">로그인</a>
				</div>
<?php		}?>
			</div>
		</div>
		<div class="inner-container">
