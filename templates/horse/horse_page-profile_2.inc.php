<?php
/** 
 * 競走馬ページ下部の情報表テンプレート
*/
?><table class="horse_base_data">
<tr><th>馬名意味</th><td><?=h($page->horse->meaning)?></td></tr>
<tr><th>備考</th><td><?=nl2br(h($page->horse->note))?></td></tr>
<tr><th>本賞金</th><td><?=h($race_history->earnings?:'')?></td></tr>
<tr><th>収得賞金</th><td><?=h($race_history->syuutoku?:'')?></td></tr>
</table>
