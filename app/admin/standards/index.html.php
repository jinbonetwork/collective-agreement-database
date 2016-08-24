		<h3>모범단협 목록</h3>
		<div class="standard-list-header">
			<form class="standard-search" action="<?php print \CADB\Lib\url("admin/standards"); ?>" method="get" onsubmit="return check_agreement_search(this);">
				<label for="agreement_q">모범단협검색</label>
				<input type="text" id="agreement_q" name="q" value="<?php print $params['q']; ?>" />
				<button type="submit">찾기</button>
			</form>
			<div class="standard-button">
				<a class="add" href="<?php print \CADB\Lib\url("admin/standards/add"); ?>">모범단협추가</a>
			</div>
		</div>
		<ul class="standards-list">
<?php if(is_array($standards)) {
		foreach($standards as $standard) {?>
			<li class="standard-item">
				<div>
					<a href="<?php print \CADB\Lib\url("admin/standards/edit").$queryString."nid=".$standard['nid']; ?>"><?php print $standard['subject']; ?></a>
					<div class="buttons">
						<a class="modify" href="<?php print \CADB\Lib\url("admin/standards/edit").$queryString."nid=".$standard['nid']; ?>">수정</a>
						<a class="delete" href="<?php print \CADB\Lib\url("admin/standards/delete").$queryString."nid=".$standard['nid']; ?>">삭제</a>
					</div>
				</div>
			</li>
<?php	}
	}?>
		</ul>
		<div class="page-nav-wrapper">
			<ul class="page-nav">
<?php	if($p_page) {?>
				<li class="p_page"><a href="<?php print $pagelink; ?>page=<?php print $p_page; ?>"><span>Prev</span></a></li>
<?php	}
		for($p=$s_page; $p<=$e_page; $p++) {
			if($p == $params['page']) {?>
				<li class="page current"><span><?php print $p; ?></span></li>
<?php		} else {?>
				<li class="page"><a href="<?php print $pagelink; ?>page=<?php print $p; ?>"><span><?php print $p; ?></span></a></li>
<?php		}
		}
		if($n_page) {?>
				<li class="n_page"><a href="<?php print $pagelink; ?>page=<?php print $n_page; ?>"><span>Next</span></a></li>
<?php	}?>
			</ul>
		</div>
		<div class="standard-button">
			<a class="add" href="<?php print \CADB\Lib\url("admin/standards/add"); ?>">모범단협추가</a>
		</div>
