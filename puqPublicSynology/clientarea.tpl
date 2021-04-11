<b>{$lang['server_address']}:</b> <h2><a
href="https://{$params['serverhostname']}:{$params['serverport']}">https://{$params['serverhostname']}:{$params['serverport']}</a></h2>
<hr>
<h3>{$lang['info_1']}</h3>
<b>{$lang['username']}:</b> {$params['username']}
<br>
<hr>
<h3>{$lang['use_of_resources_for_now']}</h3>
<b>{$lang['total']}:</b> {round($home_group_limit / '1024')} Gb <b>|</b>
<b>{$lang['used']}:</b> {round($home_used/ '1024', 2)} Gb , {round('100' * $home_used / $home_group_limit)}%
<hr>
