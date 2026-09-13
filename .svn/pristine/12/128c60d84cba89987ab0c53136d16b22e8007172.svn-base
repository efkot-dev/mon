<?php
define('TPL',true);
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$page = postCleanValue('page');
$id = postCleanValue('id');
$signal = postCleanValue('signal');
$signal = str_replace("-", '',$signal);
$term = '';
if(isset($id)){
	$getswitch = $db->Fast('switch','id,oidid',['id'=>$id]);
}
if(!empty($getswitch['id']) && isset($signal)){	
	$and_access = "AND (a.uid IS NOT NULL OR idonu IS NULL)";
	$access_sql = "LEFT JOIN checkaccess a ON CONCAT('dev', olt) = a.types AND a.uid = '{$USER['id']}'";
	$where_data = "WHERE olt = '".$getswitch['id']."' 
		AND rx IS NOT NULL 
		AND rx != '' 
		AND rx != '0' 
		AND rx BETWEEN '-" . (int)($signal).".99' AND '-" . (int)($signal) . ".00' ";

	$sqlorderby = "ORDER BY CAST(rx AS DECIMAL(10, 2)) ASC";
	$sql = "SELECT * FROM onus 
		{$access_sql}
		{$where_data}
		{$and_access}
		{$sqlorderby}";
	$sqlonus = $db->SimpleWhile($sql);
	$terminal .= '<table class="resp-tab list-onu-olt"><thead><tr>
		<th class="mob_w10" width="4%">'.$lang['status'].'</th>
		<th width="10%">'.$lang['gilka'].'</th>
		<th width="10%">MAC Serial</th>
		<th width="6%">
			<span class="inf_signal">
				<span class="sig2">RX ONU</span>
			</span>
		</th>';
		if(isset($confPMon['ONU_RX_OLT_SIGNAL']) && !empty($confPMon['ONU_RX_OLT_SIGNAL']) && $confPMon['ONU_RX_OLT_SIGNAL']==1){		
		$terminal .= '<th width="6%">
			<span class="inf_signal">
				<span class="sig1">RX OLT</span>
			</span>
		</th>';
		}
		$terminal .= '
		<th class="mobile" width="5%">'.$lang['dists'].'</th>
		<th class="mobile" width="10%">
			<span class="inf_status">
				<span class="tim1">'.$lang['sfp_2'].'</span>
				<span class="tim2">'.$lang['sfp_1'].'</span>
			</span>
		</th>';
		if(isset($confPMon['VIEW_ONU_VENDOR']) && !empty($confPMon['VIEW_ONU_VENDOR']) && $confPMon['VIEW_ONU_VENDOR']==1){
			$terminal .= '
			<th width="7%" class="mobile">Vendor</th>';
		}
		$terminal .= '
			<th>Other</th>
			</tr></thead><tbody>';
		if(isset($sqlonus) && count($sqlonus)>0){
			foreach($sqlonus as $ont){
				$status = statusTermianl($ont['status']);
				if($ont['status']==1){
					$get_status = $status['img'];
				}else{
					$get_status = reason_onu($ont['status'],$ont['reason']);
				}
				$added = checkWhenAdded($ont['added']);
				$stikers = ($ont['stikers']==1 && $active_stickers && !empty($ont['stikers'])?' stikers':'');
				$terminal .= '<tr class="'.$status['css'].''.$stikers.' '.$added.'">';
				$terminal .= '<td class="status" '.(isset($ont['reason']) ? 'id="'.$ont['reason'].'"' : '').'>'.$get_status.'</td>';
				$terminal .= '<td class="inface_onu"><a href="/?do=onu&id='.$ont['idonu'].'">'.$ont['type'].' '.$ont['inface'].'</a></td>';
				$onukey = (!empty($ont['mac'])?$ont['mac']:(!empty($ont['sn'])?$ont['sn']:null));
				if(isset($onukey)){
					$datatemponu = getFastOnusData($onukey);
				}
				$terminal .= '<td class="td_url"><a href="/?do=onu&id='.$ont['idonu'].'">'.$onukey.'</a></td>';
				// Signal Rx Onu
				$terminal .= '<td>';
				if($ont['status']==1){
					$terminal .=  signalTerminal($ont['rx']).($ont['rxstatus']=='up' || $ont['rxstatus']=='down' ? '<span class="signaldown"><i class="fi fi-rr-angle-small-'.$ont['rxstatus'].'"></i></span>':'');
				}
				$terminal .= '</td>';
				// Signal Rx Olt Onu
				if(isset($confPMon['ONU_RX_OLT_SIGNAL']) && !empty($confPMon['ONU_RX_OLT_SIGNAL']) && $confPMon['ONU_RX_OLT_SIGNAL']==1){
					$terminal .= '<td>';
					if(isset($ont['rxolt']) && $ont['status']==1){
						$terminal .=  signalTerminal($ont['rxolt']);
					}
					$terminal .= '</td>';
				}
				// Довжина волокна
				$terminal .= '<td class="dist mobile">'.($ont['dist'] ? metersToKilometers($ont['dist']) : '').'</td>';
				// Онлайн / Оффлайн
				$terminal .= '<td class="mobile">';
					if($ont['status']==1){
						$terminal .= '<span class="on_">'.aftertime($ont['online']).'</span>';
					}else{
						$terminal .= '<span class="off_">'.aftertime($ont['offline']).'</span>';
					}
				$terminal .= '</td>';
				// МОДЕЛЬ ONU
				if(isset($confPMon['VIEW_ONU_VENDOR']) && !empty($confPMon['VIEW_ONU_VENDOR']) && $confPMon['VIEW_ONU_VENDOR']==1){
					$model = (!empty($ont['vendor']) || !empty($ont['model'])?'<span class="search-model">'.$ont['model'].' '.$ont['vendor'].'</span>':'');
					$terminal .= '<td class="dist mobile">'.$model.'</td>';
				}
				// name
				$onu_name = (!empty($ont['name'])?'<span class="name-onu">'.$ont['name'].'</span>':'');
				// Tag		
				$tag = (!empty($datatemponu['tag'])?'<span class="terminaltag">'.$datatemponu['tag'].'</span>':'');
				// vendor				
				$terminal .= '<td class="description_name mobile_font">
				'.$onu_name.' '.$tag.' 
				</td>';
				$terminal .= '</tr>';
			}
		}else{
			$terminal .='<tr><td colspan="7">'.$lang['emlist'].'</td></tr>';	
		}
$terminal .='</tbody></table>';	
}
echo $terminal;
?>