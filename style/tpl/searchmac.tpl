<div id="onu-speedbar">
<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>[lang:main]</a>
<span class="brmspan"><i class="fi fi-rr-angle-left"></i>[lang:searchmac_onu]</span>
</div>
<div id="searcformhmac">
    <form style="width: 300px;">
        <input type="text" id="getmac" placeholder="MAC адрес або Серійний номер" autocomplete="off">
    </form>
</div>
<div id="searchmac">{result}</div>
<script>
document.getElementById('getmac').addEventListener('input', searchByMAC);
</script>