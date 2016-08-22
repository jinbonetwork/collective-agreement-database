			<h3>단협 목록</h3>
			<div class="article-list-header">
				<form class="article-search" action="<?php print \CADB\Lib\url("admin/articles"); ?>" method="get" onsubmit="return check_agreement_search(this);">
					<label for="agreement_q">단협검색</label>
					<input type="text" id="agreement_q" name="q" value="<?php print $params['q']; ?>" />
					<button type="submit">찾기</button>
				</form>
				<div class="article-button">
					<a class="fields" href="<?php print \CADB\Lib\url("admin/articles/fields"); ?>">필드관리</a>
					<a class="add" href="<?php print \CADB\Lib\url("admin/articles/add"); ?>">단협추가</a>
				</div>
			</div>
			<ul class="articles-list">
<?php	if(is_array($articles)) {
			foreach($articles as $article) {?>
				<li class="article-item">
					<div>
						<a href="<?php print \CADB\Lib\url("admin/articles/edit").$queryString."nid=".$article['nid']; ?>"><?php print $article['subject']; ?></a>
						<div class="buttons">
							<a class="modify" href="<?php print \CADB\Lib\url("admin/articles/edit").$queryString."nid=".$article['nid']; ?>">수정</a>
							<a class="delete" href="<?php print \CADB\Lib\url("admin/articles/delete").$queryString."nid=".$article['nid']; ?>">삭제</a>
						</div>
					</div>
				</li>
<?php		}
		}?>
			</ul>
<?php	if($total_page > 0) {?>
			<div class="page-nav-wrapper">
				<ul class="page-nav">
<?php		if($p_page) {?>
					<li class="p_page"><a href="<?php print $pagelink; ?>page=<?php print $p_page; ?>"><span>Prev</span></a></li>
<?php		}
			for($p=$s_page; $p<=$e_page; $p++) {
				if($p == $params['page']) {?>
					<li class="page current"><span><?php print $p; ?></span></li>
<?php			} else {?>
					<li class="page"><a href="<?php print $pagelink; ?>page=<?php print $p; ?>"><span><?php print $p; ?></span></a></li>
<?php			}
			}
			if($n_page) {?>
					<li class="n_page"><a href="<?php print $pagelink; ?>page=<?php print $n_page; ?>"><span>Next</span></a></li>
<?php		}?>
				</ul>
			</div>
<?php	}?>
			<div class="article-button">
				<a class="add" href="<?php print \CADB\Lib\url("admin/articles/add"); ?>">단협추가</a>
			</div>
