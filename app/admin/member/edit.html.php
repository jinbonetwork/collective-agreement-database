<div id="member-editor-page">
	<div class="member-info-container">
		<div class="member-info">
			<div class="header">
				<div class="member-name">회원 수정하기</div>
			</div>
			<div class="content">
<?php			\CADB\View\Component::getComponent( $edit_component, array( 'mode'=>'modify', 'member'=>$member ) ); ?>
			</div>
		</div>
	</div>
</div>
