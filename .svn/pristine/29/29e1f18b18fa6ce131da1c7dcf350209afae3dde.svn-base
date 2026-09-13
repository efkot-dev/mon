<div id="blockvlan" style="display: grid;width: 99%;margin: 0;padding: 0; grid-template-columns: repeat(2, 1fr);">
    <div class="blockvlan">
        <div class="ont-label">
            <div class="name-label">
                <img class="man_vlan" src="../style/img/eth.png">Onu-vlan-mode transparent
            </div>
            <div class="data-label">
                <form id="vlanForm_Transparent" method="post">
                    <div id="tag">
						<input type="hidden" name="do" value="telnet">
						<input type="hidden" name="act" value="change_vlan_telnet">
						<input type="hidden" name="gvan" value="transparent">
                        <input type="hidden" name="idonu" value="{{idonu}}">
                        <input type="hidden" name="olt" value="{{olt}}">
                        <button type="submit" class="btn-tag">{{lang_edit}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>    
	<div class="blockvlan">
        <div class="ont-label">
            <div class="name-label">
                <img class="man_vlan" src="../style/img/eth.png">VLAN
            </div>
            <div class="data-label">
                <form id="vlanForm" method="post">
                    <div id="tag">
                        <input type="hidden" name="do" value="telnet">
                        <input type="hidden" name="act" value="change_vlan_telnet">
                        <input class="input" type="text" name="vlan" value="{{vlan}}">
                        <input type="hidden" name="idonu" value="{{idonu}}">
                        <input type="hidden" name="olt" value="{{olt}}">
                        <button type="submit" class="btn-tag">{{lang_edit}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function(){
    $('#vlanForm_Transparent, #vlanForm').on('submit', function(e){
        e.preventDefault();
        $("#loading").show();
        $.ajax({
            url: '/?do=telnet',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                $("#loading").hide();
				if (response.trim() === 'ok') {
                    location.reload(); 
                }
            }
        });
    });
});
</script>


