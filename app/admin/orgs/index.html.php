		<h3>조직 목록</h3>
		<div class="orgs-list-header">
			<form class="orgs-search" action="<?php print \CADB\Lib\url("admin/orgs"); ?>" method="get" onsubmit="return check_orgs_search(this);">
				<label for="orgs_q">조직검색</label>
				<input type="text" id="orgs_q" name="q" value="<?php print $params['q']; ?>" />
				<button type="button" class="option">옵션</button>
				<div id="org-search-option" class="collapsed">
					<div class="org-search-option-container">
<?php			foreach($fields as $field) {
					if($field['type'] == 'taxonomy') {?>
						<fieldset data-q="o<?php print substr($field['field'],1); ?>">
							<legend><?php print $field['subject']; ?></legend>
							<ul>
<?php 						foreach($taxonomy_terms[$field['cid']] as $tid => $term) {
								$checked = "";
								if($field['multiple']) {
									if(@in_array($tid,$params['o'.substr($field['field'],1)]))
										$checked = " checked";
								} else {
									if($tid == $params['o'.substr($field['field'],1)])
										$checked = " checked";
								}?>
								<li>
									<input id="<?php print $field['cid'];?>-<?php print $tid; ?>" name="o<?php print substr($field['field'],1); ?><?php print ($field['multiple'] ? '[]' : ''); ?>" type="<?php print ($field['multiple'] ? 'checkbox' : 'radio'); ?>" value="<?php print $tid; ?>"<?php print $checked; ?> />
									<label for="<?php print $field['cid'];?>-<?php print $tid; ?>"><?php print $term['name']; ?></label>
								</li>
<?php						}?>
							</ul>
						</fieldset>
<?php				}
				}?>
					</div>
				</div>
				<button type="submit">찾기</button>
			</form>
			<div class="article-button">
				<a class="fields" href="<?php print \CADB\Lib\url("admin/orgs/fields"); ?>">필드관리</a>
				<a class="add" href="<?php print \CADB\Lib\url("admin/orgs/add"); ?>">조직추가</a>
				<a class="excel" href="<?php print \CADB\Lib\url("admin/orgs/excel"); ?>">Excel다운로드</a>
			</div>
		</div>
		<table class="orgs-list" border="0" cellspacing="0" cellpadding="0" width="100%">
			<thead>
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
<?php	if( is_array($orgs) ) {
			foreach($orgs as $org) {?>
				<tr>
					<td class="nojo"><?php print $org['nojo']; ?></td>
					<td class="sub1"><?php print $org['sub1']; ?></td>
					<td class="sub2"><?php print $org['sub2']; ?></td>
					<td class="sub3"><?php print $org['sub3']; ?></td>
					<td class="sub4"><?php print $org['sub4']; ?></td>
					<td class="f7"><?php print $org['f8']; ?></td>
					<td class="f8"><?php print $org['f9']; ?></td>
					<td class="modify">
						<a href="<?php print \CADB\Lib\url("admin/orgs/edit").$queryString."oid=".$org['oid']; ?>">수정</a>
					</td>
					<td class="delete">
						<a href="<?php print \CADB\Lib\url("admin/orgs/delete").$queryString."oid=".$org['oid']; ?>">삭제</a>
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
			<a class="add" href="<?php print \CADB\Lib\url("admin/orgs/add"); ?>">조직추가</a>
			<a class="excel" href="<?php print \CADB\Lib\url("admin/orgs/excel"); ?>">Excel다운로드</a>
		</div>
