<div id="blockvlan" style="display: block;">
    <div class="blockvlan">
        <div class="ont-label">
            <div class="name-label">
                <img class="man_vlan" src="../style/img/eth.png">VLAN
            </div>
            <div class="data-label">
                <form id="vlanForm">
                    <div id="tag">
                        <input type="hidden" name="act" value="change_vlan_telnet">
                        <input class="input" type="text" name="vlan" value="1">
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
    $('#vlanForm').on('submit',function(e){
	$("#loading").show();
        e.preventDefault();
        $.post('/?do=telnet',$(this).serialize(),function(response){
            $('#ont').html(response);
			location.reload();
        });
    });
});
</script>
