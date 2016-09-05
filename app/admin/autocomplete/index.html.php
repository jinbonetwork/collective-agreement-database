		<h3>자동완성 데이터베이스 재설정</h3>
		<div id="autocomplete-admin-page">
			<div class="autocomplete-admin-container">
				<div class="manual">
					<p>자동완성기능은 아래 필드들을 대상으로 만들어집니다.</p>
					<ul class="autocomplete-items">
						<li>조직데이터베이스 : 노조명, 본부명, 지부명, 지회명, 분회명</li>
<?php			if(is_array($fields)) {
					foreach($fields as $f) {?>
						<li>
<?php 					switch($f['table']) {
							case "organize":
								echo "조직데이터베이스";
								break;
							case "agreement":
								echo "단협데이터베이스";
								break;
							case "guide":
								echo "모범단협데이터베이스";
								break;
						}?>
							:
<?php						print $f['subject']; ?>
						</li>
<?php				}
				}
				if(is_array($taxonomy)) {
					foreach($taxonomy as $tx) {?>
						<li>분류 : <?php print $tx['subject']; ?></li>
<?php				}
				}?>
					</ul>
					<p>추가 필드나 분류를 자동완성에 포함시키려면 각각의 필드관리에서 지정하세요.</p>
				</div>
				<div class="reset">
					<p>
						<ul class="autocomplete-manual">
							<li>자동완성 데이터베이스는 매일 새벽에 자동 리빌딩됩니다.</li>
							<li>위 항목에 해당되는 내용을 수정하여 바로 자동완성에 반영하게 하시려면 아래 '자동왼성 데이터베이스 재설정하기' 버튼을 클릭하세요</li>
						</ul>
					</p>
					<button>자동완성 데이터베이스 재설정하기</button>
				</div>
			</div>
		</div>
