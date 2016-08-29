        <h3><?php print $guide['subject']; ?> 수정하기</h3>
		<div class="guide-edit" data-nid="<?php print $guide['nid']; ?>">
			<div class="guide-info-container">
				<h4 class="sub-title">모범단협 기본정보</h4>
				<form method="POST" action="">
					<input type="hidden" name="table" value="guide" />
					<input type="hidden" name="mode" value="modify" />
					<input type="hidden" name="nid" class="cadb-guide-column" value="<?php print $guide['nid']; ?>" tabindex="1" />
					<fieldset>
						<label class="field-title" for="guide-subject">제목</label>
						<input type="text" name="subject" id="guide-subject" class="cadb-guide-column" tabindex="2" value="<?php print $guide['subject']; ?>" />
					</fieldset>
					<fieldset>
						<label class="field-title" for="guide-year">년도</label>
						<input type="text" name="year" id="guide-year" class="year cadb-guide-column" tabindex="3" value="<?php print $guide['year']; ?>" />년
					</fieldset>
					<fieldset>
						<label class="field-title" for="guide-content">요약</label>
						<textarea name="content" id="guide-content" class="cadb-guide-column" tabindex="4"><?php print $guide['content']; ?></textarea>
					</fieldset>
					<fieldset>
						<label class="field-title" for="guide-cid">분류</label>
<?php				foreach($taxonomylist as $cid => $taxo) {?>
						<input type="checkbox" name="cid" id="guide-cid-<?php print $cid; ?>" value="<?php print $cid; ?>"<?php print (in_array($cid,$current_taxonomys) ? " checked": ""); ?>><label for="guide-cid-<?php print $cid; ?>"><?php print $taxo['subject']; ?></label>
<?php				}?>
					</fieldset>
					<fieldset>
						<label class="field-title" for="guide-current">기본</label>
						<input type="checkbox" name="current" id="guide-current" value="1"<?php print ($guide['current'] ? ' checked' : ''); ?> />
						<label for="guide-current">이 모범단협을 기본단협으로 지정합니다.</label>
					</fieldset>
					<button type="button" class="delete">삭제</button>
					<button type="submit">저장</button>
				</form>
			</div>
			<div class="guide-clause-container">
				<h4 class="sub-title">세부조항</h4>
				<div class="guide-clause-indexes-wrap">
					<div class="guide-clause-indexes">
						<label class="">목차</label>
						<div class="guide-clause-indexes-box">
							<dl id="guide-chapter-0" class="dummy" data-id="0" data-parent="0" data-index="0">
								<dt class="chapter-title" data-id="0"></dt>
								<dd class="console chapter-add-console">
									<button type="button" class="clause-add">챕터추가</button>
								</dd>
								<dd class="chapter-articles">
									<article id="" data-id="" data-parent="0" data-index="0" class="dummy">
										<div class="console article-add-console">
											<button type="button" class="clause-add">조항추가</button>
										</div>
									</article>
								</dd>
							</dl>
<?php				if(is_array($indexes)) {
						foreach($indexes as $id => $index) {?>
							<dl id="guide-chapter-<?php print $index['id']; ?>" class="collapsed" data-id="<?php print $index['id']; ?>" data-parent="0" data-index="<?php print $index['idx']; ?>">
								<dt class="chapter-title" data-id="<?php print $index['id']; ?>"><?php print $index['subject']; ?></dt>
								<dd class="console chapter-add-console">
									<button type="button" class="clause-add">챕터추가</button>
								</dd>
								<dd class="chapter-articles">
									<article id="" data-id="" data-parent="<?php print $index['id']; ?>" data-index="0" class="dummy">
										<div class="console article-add-console">
											<button type="button" class="clause-add">조항추가</button>
										</div>
									</article>
<?php						if($index['nsubs']) {?>
<?php							foreach($indexes[$id]['articles'] as $article) {?>
									<article id="guide-article-<?php print $article['id']; ?>" data-id="<?php print $article['id']; ?>" data-parent="<?php print $article['parent']; ?>" data-index="<?php print $article['idx']; ?>" class="article">
										<span><?php print $article['subject']; ?></span>
										<div class="console article-add-console">
											<button type="button" class="clause-add">조항추가</button>
										</div>
									</article>
<?php							}?>
<?php						}?>
								</dd>
							</dl>
<?php					}
					}?>
						</div>
					</div>
				</div>
				<div class="guide-clause-document-wrap">
					<div class="guide-clause-document" data-id="<?php print $preamble['id']; ?>" data-parent="<?php print $preamble['parent']; ?>" data-index="<?php print $preamble['idx']; ?>">
						<div class="delete-message">
							<span>삭제되었습니다.</span>
						</div>
						<h3 id="guide-clause-subject" contenteditable="true" placeholder="조항제목을 입력하세요"><?php print $preamble['subject']; ?></h3>
						<div id="guide-clause-taxonomy">
<?php					foreach($taxonomy_terms as $cid => $taxonomies) {?>
							<div id="guide-clause-taxonomy-<?php print $cid; ?>" class="guide-clause-taxonomy-item" data-cid="<?php print $cid; ?>">
								<label><?php print $taxonomy[$cid]['subject']; ?> 분류</label>
								<div class="guide-clause-taxonomy-value" data-tid="0">[<?php print $taxonomy[$cid]['subject'];?>] 선택</div>
								<ul class="guide-clause-taxonomy-list" data-cid="<?php print $cid; ?>">
<?php					foreach($taxonomies[0] as $t => $taxo) {?>
									<li data-tid="<?php print $t; ?>" data-vid="<?php print $taxo['vid'] ;?>" data-parent="0" class="<?php print ($preamble['terms'][$t] ? "selected" : ""); ?><?php print ($taxo['parent'] ? " sub" : ""); ?>"><div class="taxonomy-name"><?php print $taxo['name']; ?></div><div class="taxonomy-control"><i class="fa fa-gear" title="관리패널열기"></i></div></li>
<?php						if($taxo['nsubs']) {
								foreach($taxonomies[$t] as $tt => $taxoo) {?>
									<li data-tid="<?php print $tt; ?>" data-vid="<?php print $taxoo['vid']; ?>" data-parent="<?php print $t; ?>" class="<?php print ($preamble['terms'][$tt] ? "selected" : ""); ?><?php print ($taxoo['parent'] ? " sub" : ""); ?>"><div class="taxonomy-name"><?php print $taxoo['name']; ?></div><div class="taxonomy-control"><i class="fa fa-gear" title="관리패널열기"></i></div></li>
<?php							}
							}
						}?>
								</ul>
							</div>
<?php					}?>
						</div>
						<div id="guide-clause-content">
<?php						print $preamble['content']; ?>
						</div>
<?php 			foreach($fields as $fid => $field) {
					if($field['table'] == 'guide_clause') {?>
						<div id="guide-clause-field-f<?php print $fid; ?>" class="guide-clause-field<?php print ($preamble['parent'] ? ' article' : ' parent') ?>" data-fid="<?php print $fid; ?>">
							<h4><?php print $field['subject']; ?></h4>
							<div id="guide-clause-field-f<?php print $fid; ?>-content" class="guide-clause-field-content">
<?php							print $preamble['f'.$fid]; ?>
							</div>
						</div>
<?php				}
				}?>
						<div class="button">
							<button class="modify">저장</button>
							<button class="delete">삭제</button>
						</div>
					</div>
				</div>
			</div>
		</div>
