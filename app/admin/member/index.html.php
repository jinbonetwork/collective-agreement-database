		<h3>회원 목록</h3>
		<div class="member-list-header">
			<form class="member-search" action="<?php print \CADB\Lib\url("admin/member"); ?>" method="get" onsubmit="return check_member_search(this);">
				<label for="member_q">회원검색</label>
				<select name="s_mode">
					<option value="mb_id"<?php print($params['s_mode'] == 'mb_id' ? ' selected': ''); ?>>아이디</option>
					<option value="mb_name"<?php print($params['s_mode'] == 'mb_name' ? ' selected': ''); ?>>이름</option>
					<option value="mb_nick"<?php print($params['s_mode'] == 'mb_nick' ? ' selected': ''); ?>>닉네임</option>
				</select>
				<input type="text" id="member_q" name="s_arg" value="<?php print $params['s_arg']; ?>" />
				<button type="submit">찾기</button>
			</form>
			<div class="article-button">
				<a class="add" href="<?php print \CADB\Lib\url("admin/member/add"); ?>">회원추가</a>
			</div>
		</div>
		<table class="member-list" border="0" cellspacing="0" cellpadding="0" width="100%">
			<thead>
				<col class="mb_id" />
				<col class="mb_name" />
				<col class="mb_nick" />
				<col class="mb_level" />
				<col class="mb_email" />
				<col class="roles" />
				<col class="modify" />
				<col class="delete" />
			</thead>
			<tbody>
				<tr>
					<th class="mb_id">아이디</th>
					<th class="mb_name">이름</th>
					<th class="mb_nick">닉네임</th>
					<th class="mb_level">레벨</th>
					<th class="mb_email">이메일</th>
					<th class="roles">담당조직</th>
					<th class="modify">수정</th>
					<th class="delete">삭제</th>
				</tr>
<?php	if( is_array($members) ) {
			foreach($members as $mb_id => $member) {?>
				<tr>
					<td class="nojo"><?php print $member['mb_id']; ?></td>
					<td class="mb_name"><?php print $member['mb_name']; ?></td>
					<td class="mb_nick"><?php print $member['mb_nick']; ?></td>
					<td class="mb_level"><?php print $member['level_name']; ?></td>
					<td class="mb_email"><?php print $member['mb_email']; ?></td>
					<td class="roles">
<?php 				if($member['roles'] && count($member['roles']) > 0) {?>
						<ul class="role">
<?php					foreach($member['roles'] as $oid => $role) {?>
							<li><?php print $role['fullname']; ?></li>
<?php					}?>
						</ul>
<?php				}?>
					</td>
					<td class="modify">
						<a href="<?php print \CADB\Lib\url("admin/member/edit").$queryString."mb_no=".$member['mb_no']; ?>">수정</a>
					</td>
					<td class="delete">
						<a href="<?php print \CADB\Lib\url("admin/member/delete").$queryString."mb_no=".$member['mb_no']."&output=redirect"; ?>">삭제</a>
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
