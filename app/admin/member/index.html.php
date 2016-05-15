		<h3>회원 목록</h3>
		<div class="member-list-header">
			<form class="member-search" action="<?php print \CADB\Lib\url("admin/member"); ?>" method="get" onsubmit="return check_member_search(this);">
				<label for="member_q">회원검색</label>
				<input type="text" id="member_q" name="q" value="<?php print $params['q']; ?>" />
				<button type="submit">찾기</button>
			</form>
			<div class="article-button">
				<a class="add" href="<?php print \CADB\Lib\url("admin/member/add"); ?>">회원추가</a>
			</div>
		</div>
		<table class="member-list" border="0" cellspacing="0" cellpadding="0" width="100%">
			<thead>
				<col class="mb_id" />
				<col class="nojo" />
				<col class="sub1" />
				<col class="sub2" />
				<col class="sub3" />
				<col class="sub4" />
				<col class="f9" />
				<col class="f9" />
				<col class="modify" />
				<col class="delete" />
			</thead>
			<tbody>
				<tr>
					<th class="nojo">아이디</th>
					<th class="nojo">노조</th>
					<th class="sub1">본부</th>
					<th class="sub2">지부</th>
					<th class="sub3">지회</th>
					<th class="sub4">분회</th>
					<th class="f8">원청</th>
					<th class="f9">하청</th>
					<th class="modify">수정</th>
					<th class="delete">삭제</th>
				</tr>
<?php	if( is_array($members) ) {
			foreach($members as $member) {?>
				<tr>
					<td class="nojo"><?php print $member['mb_id']; ?></td>
					<td class="nojo"><?php print $member['nojo']; ?></td>
					<td class="sub1"><?php print $member['sub1']; ?></td>
					<td class="sub2"><?php print $member['sub2']; ?></td>
					<td class="sub3"><?php print $member['sub3']; ?></td>
					<td class="sub4"><?php print $member['sub4']; ?></td>
					<td class="f7"><?php print $member['f8']; ?></td>
					<td class="f8"><?php print $member['f9']; ?></td>
					<td class="modify">
						<a href="<?php print \CADB\Lib\url("admin/member/edit").$queryString."oid=".$org['oid']; ?>">수정</a>
					</td>
					<td class="delete">
						<a href="<?php print \CADB\Lib\url("admin/member/delete").$queryString."nid=".$org['oid']; ?>">삭제</a>
					</td>
				</tr>
<?php		}
		}?>
			</tbody>
		</table>
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
		<div class="article-button">
			<a class="add" href="<?php print \CADB\Lib\url("admin/member/add"); ?>">회원추가</a>
		</div>
