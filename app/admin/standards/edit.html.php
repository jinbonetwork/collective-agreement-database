        <h3><?php print $guide['subject']; ?> 수정하기</h3>
		<div class="guide-edit">
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
<?php					foreach($indexes as $id => $index) {?>
							<dl id="guide-chapter-<?php print $index['id']; ?>" class="collapsed">
								<dt class="chapter-title" data-id="<?php print $index['id']; ?>"><?php print $index['subject']; ?></dt>
								<dd class="console chapter-add-console">
									<button type="button" class="clause-add">챕터추가</button>
								</dd>
								<dd class="chapter-articles">
									<article id="" data-id="" data-parent="<?php print $index['id']; ?>" data-index="i" class="article">
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
<?php					}?>
						</div>
					</div>
				</div>
				<div class="guide-clause-document-wrap">
					<div class="guide-clause-document" data-id="<?php print $preamble['id']; ?>" data-parent="<?php print $preamble['parent']; ?>" data-index="<?php print $preamble['idx']; ?>">
						<h3 id="guide-clause-subject" contenteditable="true"><?php print $preamble['subject']; ?></h3>
						<div id="guide-clause-taxonomy">
<?php					foreach($taxonomy_terms as $cid => $taxonomies) {?>
							<select id="guide-clause-taxonomy-<?php print $cid; ?>" data-cid="<?php print $cid; ?>">
								<option value="0">[<?php print $taxonomy[$cid]['subject'];?>] 선택</option>
<?php						foreach($taxonomies as $t => $taxo) {?>
								<option value="<?php print $t; ?>"<?php print ($preamble['terms'][$t] ? " selected" : ""); ?>><?php print $taxo['name']; ?></option>
<?php						}?>
							</select>
<?php					}?>
						</div>
						<div id="guide-clause-content">
<?php						print $preamble['content']; ?>
						</div>
<?php 			foreach($fields as $fid => $field) {
					if($field['table'] == 'guide_clause') {?>
						<div id="guide-clause-field-f<?php print $fid; ?>" class="guide-clause-field<?php print ($preamble['parent'] ? ' article' : ' parent') ?>" data-fid="<?php print $fid; ?>">
							<h4><?php print $field['subject']; ?></h4>
							<div class="guide-clause-field-content">
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
