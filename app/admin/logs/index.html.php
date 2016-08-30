		<h3>로그</h3>
		<div class="logs-list-header">
		</div>
		<table class="logs-list" border="0" cellspacing="0" cellpadding="0" width="100%">
			<thead>
				<col class="table" />
				<col class="type" />
				<col class="oid" />
				<col class="editor" />
				<col class="modified" />
				<col class="ipaddress" />
				<col class="memo" />
			</thead>
			<tbody>
				<tr>
					<th class="table">구분</th>
					<th class="type">행동</th>
					<th class="oid">대상</th>
					<th class="editor">작업자</th>
					<th class="modified">작업일</th>
					<th class="ipaddress">IP</th>
					<th class="memo">메모</th>
				</tr>
<?php	if(is_array($logs)) {
			foreach($logs as $log) {?>
				<tr>
					<td class="table" nowrap valign="top"><?php print $log['table']; ?></td>
					<td class="type" nowrap valign="top"><?php print $log['type']; ?></td>
					<td class="oid" nowrap valign="top"><?php print $this->viewArticle($log); ?></td>
					<td class="editor" nowrap valign="top"><?php print $this->viewMember($log); ?></td>
					<td class="modified" nowrap valign="top"><?php print date("Y-m-d H:i:s",$log['modified']); ?></td>
					<td class="ipaddress" nowrap valign="top"><?php print $log['ipaddress']; ?></td>
					<td class="memo" valign="top"><?php print nl2br($log['memo']); ?></td>
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
