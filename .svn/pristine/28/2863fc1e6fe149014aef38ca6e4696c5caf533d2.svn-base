<div id="onu-speedbar">
<a class="brmhref" href="/?do=detail&act=olt&id={id}"><i class="fi fi-rr-apps"></i>{name}</a>
<span class="brmspan"><i class="fi fi-rr-angle-left"></i>{port}</span>
</div>
{result}
<div id="port-onu"></div>
<script>
$(document).ready(function() {
	$('#switch').on('click', 'div[id^="port-"]', function() {
		var portid = $(this).attr('id').split('-')[1];
		ajaxloadonu(portid);
	});
});
</script>