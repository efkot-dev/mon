<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$bdcom_epon_acdc_pw = array(
	1=>'power-A-normal',
	2=>'power-B-normal',
	3=>'power-A-B-normal',
	4=>'other'
);
$huawei_slot_types = array(
    'h803epfd' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>H803EPFD</b>EPON 16 </span></span>',
    'h806gpbd' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>H806GPBD</b>GPON 8 </span></span>',
    'h801x2cs' => '<span class="slotplat_huawei"><img src="../style/img/ethernetslot.png"><span><b>H801X2CS</b>2/10GE</span></span>',
    'h801mcud' => '<span class="slotplat_huawei"><img src="../style/img/ethernetslot.png"><span><b>H801MCUD</b>4x100/1000Mbps</span></span>',
    'h801mpwd' => '<span class="slotplat_huawei"><img src="../style/img/danger.png"><span><b>H801MPWD</b> 400W</span></span>',
    'h809epbd' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>H809EPBD</b>EPON 8</span></span>',
    'h802scun' => '<span class="slotplat_huawei"><img src="../style/img/ethernetslot.png"><span><b>H802SCUN</b>4x100/1000Mbps</span></span>',
    'h801gicf' => '<span class="slotplat_huawei"><img src="../style/img/ethernetslot.png"><span><b>H801GICF</b>2x100/1000Mbps</span></span>',
    'h805gpfd' => '<span class="slotplat_huawei"><img src="../style/img/ethernetslot.png"><span><b>H805GPFD</b>16x GPON, 2.5G</span></span>',
    'h801mcud1' => '<span class="slotplat_huawei"><img src="../style/img/ethernetslot.png"><span><b>H801MCUD1</b>MCUD1 10G</span></span>'
);
$zte_slot_types = array(
    'smxa' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>SMXA/3</b> (GE/FE,10GE/GE)</span></span>',
    'etto' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>ETTO</b> 10G EPON 8</span></span>',
    'ettok' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>ETTOK</b> 10G EPON 8</span></span>',
    'gvgh' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>GVGH</b> GPON 16</span></span>',
    'gmra' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>GMRA</b> ZTE</span></span>',
    'fumo' => '<span class="slotplat_huawei"><img src="../style/img/danger.png"><span><b>Fan card</b> 4*FAN</span></span>',
    'etgod' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>GTGOD</b> GPON 16</span></span>',
    'gtgo' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>GTGO</b> GPON 16</span></span>',
    'gtgh' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>GTGH</b> GPON 16</span></span>',
    'prwg' => '<span class="slotplat_huawei"><img src="../style/img/danger.png"><span><b>ZXA10 PRWG</b> 2.5W</span></span>',
    'gtghk' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>GTGHK</b> GPON 16</span></span>',
    'ettod' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>ETTO</b> 10G EPON 8</span></span>',
    'gufq' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>GUFQ</b> 4 GE/SFP+</span></span>',
    'scxm' => '<span class="slotplat_huawei"><img src="../style/img/network.png"><span><b>SCXN</b> 3GE,4SFP</span></span>',
    'pram' => '<span class="slotplat_huawei"><img src="../style/img/danger.png"><span><b>PRAM</b> 220V — 48VDC</span></span>'
);
?>
