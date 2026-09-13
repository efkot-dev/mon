<div class="flex-2-left">
<div class="block-search-main">
	<div class="search-main">
		<form role="search" method="get" class="searchform" >	
			<input type="text" class="search" name="zapros" id="search_olt" value="" autocomplete="off" placeholder="[lang:search_title]">
			<input type="hidden" name="typedevice" id="typedevice" value="{typedevice}">		
		</form>		
	</div>{view_list}{clear}
</div>
<div class="block-sort-device" id="view_block">
<div class="sort">
<a href="{sort_name}"><i class="fi fi-rr-letter-case"></i>[lang:name]</a>
<a href="{sort_time}"><i class="fi fi-rr-user-time"></i>[lang:pmonchecker]</a>
<a href="{sort_ip}"><i class="fi fi-rr-user-time"></i>[lang:ip]</a>
{sort_location}
{sort_all}
</div>
</div>
<script>
$('#view_list').on('click', function() {
	$('#view_block').toggle();
});
</script>
<div class="block-lite w80" id="result-ajax">
<div class="block-center">
<div class="content">
<div id="device">
<table cellspacing="0" cellpadding="3" width="100%" id="page_device">
{result}
</table>
</div>
</div>
<div class="pager">{pagerbottom}</div>
</div>
</div>
</div>
