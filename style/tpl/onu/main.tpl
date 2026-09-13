<div id="onu-speedbar">
	<a class="brmhref" href="/?do=detail&act=olt&id={olt_id}"><i class="fi fi-rr-apps"></i>{olt_place}</a>
	<a class="brmhref" href="/?do=terminal&id={olt_id}&port={port_id}"><i class="fi fi-rr-angle-left"></i>{olt_port_ont}</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>{inface_ont}</span>
</div>
<div class="ont-sys">
	<div class="ontmac" onclick="copyToClipboard()">{number_ont}</div>
	<div class="ontinface"><span class="n">{type_ont}</span><span class="m">{inface}</span></div>
	{nameonu}
	{templates_sheduler}
</div>
{bandwidth}
<div id="block-ont-{id}"></div>
<div class="pmon_onu">
	<div class="pmon_onu_left">
		<div id="ontpmon"></div>
		<div id="panel-ont"></div>
		<div class="onu-data efect1 m20b">
		<div id="ont"><img src="../style/img/load.gif"></div>
		</div>
		<div id="ponbox_{id}" class="fibers"></div>		
		<div id="onu_equipment"></div>		
		<div id="historysignal"></div>
		{map}
	</div>	
	<div class="pmon_onu_right">
		<div id="trafficport"></div>
		<div id="pir_speed"></div>
		<div id="transport_onu"></div>
		{block_list_signal}
		<div id="ajaxstikers">{bookmarks}</div>
		<div class="onu-olt efect1 m20b">
			<div id="ajax-billing"></div>		
			<div class="ont-base">{billing}{tag}{comments}{logonu}</div>
		</div>
	</div>
</div>
<div id="ont-{id}"></div>
<script type="text/javascript">
ajaxont({id});
ajaxpanel({id});
onu_equipment({id});
{ajaxbilling}
{ajaxsignal}
{ajaxstikers}
{ajaxponbox}
{ajaxtransportonu}
</script>
<script src="../style/js/scheduler.js?do=s"></script>
