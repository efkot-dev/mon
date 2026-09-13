<?php
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';

$idonu = Clean::int($_POST['idonu'] ?? null);
$dataidonu = Clean::int($_POST['dataid'] ?? null);
$act = Clean::str($_POST['act'] ?? null);

$where = [];
if ($act === 'view' && isset($idonu)) {
    $onukey = $dataonu['mac'] ?? $dataonu['sn'] ?? null;

    if (empty($dataidonu)) {
        $where['onukey'] = $onukey;
    } else {
        $where['id'] = $dataidonu;
    }

    if (!empty($where)) {
        $datatemponu = $db->Fast('onusdata', '*', $where);
        if (!empty($datatemponu['id']) && !empty($datatemponu['ponelement'])) {
            $data_ponelement = $db->Fast('ponelement', '*', ['id' => $datatemponu['ponelement']]);
            $data_pontree = $db->Fast('pontree', '*', ['id' => $datatemponu['pontree']]);
            if (!empty($data_ponelement['id'])) {
                $dataonu1 = $db->SimpleWhile("
                    SELECT onusdata.onukey, onus.idonu, onus.inface, onus.rx, onus.dist, onus.status, onus.olt, onus.type
                    FROM onusdata
                    JOIN onus ON onusdata.onukey = onus.mac OR onusdata.onukey = onus.sn
                    WHERE onusdata.ponelement = '{$datatemponu['ponelement']}'
                      AND onusdata.pontree = '{$datatemponu['pontree']} '
                ");
                if (!empty($dataonu1)) {
                    echo '<div class="fiber_module">';
                    echo '<div id="fiber-pon-name">';
					echo '<div class="block-left">';
					echo'<a class="link" href="/?do=fiber&act=viewtree&id='.$data_pontree['id'].'"><span>'.$lang['pon_network'].':</span> '.$data_pontree['name'].'<img src="../style/img/link.png"></a>';
					echo'<a class="link" href="#"><span>FOB:</span> '.$data_ponelement['name'].'<img src="../style/img/link.png"></a>';
					echo '</div>';
					echo '<div class="block-right">';
					echo '<a class="link del-conn" href="#" 
					href="#" onclick="clear_fibermap(event,'.$idonu.','.$datatemponu['id'].',\''.$lang['fiber_del_conn'].'\');"
					<img src="../style/img/close.png">'.$lang['pon_network_disconect'].'</a>';
					echo '</div>';
                    echo '</div>';
                    echo '<div id="fiber-pon">';
                    echo '<div class="list-ponbox">';
                    foreach ($dataonu1 as $onu_) {
                        $curent = (trim($onu_['onukey']) === trim($datatemponu['onukey'])) ? ' curent_onu' : '';
                        $status = ($onu_['status'] == 1) ? '-online' : '-offline';
                        echo '<a href="/?do=onu&id=' . $onu_['idonu'] . '" class="url-fiber' . $curent . '">';
                        echo '<div class="fiber-onu">';
                        echo '<div class="ponimg">';
                        echo '<img src="../style/img/onu.png">';
                        echo '<div class="signal">' . ($onu_['status'] == 1 ? signalTerminal($onu_['rx']) : '') . '</div>';
                        echo '</div>';
                        echo '<div class="onu-name' . $status . '">' . htmlspecialchars($onu_['onukey']) . '</div>';
                        echo '<div class="onu-inface' . $status . '">' . htmlspecialchars($onu_['inface']) . '</div>';
                        echo '</div></a>';
                    }
                    echo '</div></div></div>';
                }
            }else{
				
			}
        }
    }
}
?>
