<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class Mapper {
    private $pdo;
    private $confPMon;
    private $lang;    
    private $config;    
    public function __construct($pdo, $lang, $confPMon, $config) {
        $this->pdo = $pdo;
        $this->confPMon = $confPMon;
        $this->lang = $lang;
        $this->config = $config;
    }
    public function icon_location($location) {
		return "L.marker([".$location['lan'].",".$location['lon']."], {icon: map_location})".
			".bindPopup('<div class=\"div-l\"><h2>".$location['name']."</h2></div>".
			"<div class=\"div-l\"><a href=\"/?do=map&location=".$location['id']."\">".$this->lang['show']."</a></div>')".
			".addTo(map);
";
	}
    public function map_js() {
		return'<link rel="stylesheet" href="../style/map/leaflet.css" />
	<script src="../style/map/leaflet.js"></script>
	<script src="../style/map/mymarker.js?fd=334"></script>';
	}
    public function onu_status_offline_icon($onu) {
		if(!empty($onu['ont']['reason'])){
			$reason = $onu['ont']['reason'];
			if($reason=='err1'){
				return "var onu_{$onu['ont']['idonu']} = L.marker([".$onu['lan'].",".$onu['lon']."], {icon: map_onu_err1})";	
			}elseif($reason=='err61'){
				return "var onu_{$onu['ont']['idonu']} = L.marker([".$onu['lan'].",".$onu['lon']."], {icon: map_red_box})";	
			}elseif($reason=='err34'){
				return "var onu_{$onu['ont']['idonu']} = L.marker([".$onu['lan'].",".$onu['lon']."], {icon: map_onu_err34})";	
			}elseif($reason=='err0'){
				return "var onu_{$onu['ont']['idonu']} = L.marker([".$onu['lan'].",".$onu['lon']."], {icon: map_onu_err59})";	
			}elseif($reason=='err6'){
				return "var onu_{$onu['ont']['idonu']} = L.marker([".$onu['lan'].",".$onu['lon']."], {icon: map_onu_err6})";	
			}elseif($reason=='err8'){
				return "var onu_{$onu['ont']['idonu']} = L.marker([".$onu['lan'].",".$onu['lon']."], {icon: map_onu_err6})";	
			}elseif($reason=='err59'){
				return "var onu_{$onu['ont']['idonu']} = L.marker([".$onu['lan'].",".$onu['lon']."], {icon: map_onu_err59})";
			}
		}
		return "var onu_{$onu['ont']['idonu']} = L.marker([".$onu['lan'].",".$onu['lon']."], {icon: map_onu_offline})";
	}
	public function signal_class($signal) {
		$signalbadstart = $this->config['badsignalstart'] ?? 26;
		$signalbadend = $this->config['badsignalend'] ?? 39;
		$signala = (int)str_replace('-', '', $signal);
		if ($signala >= 1 && $signala <= 12) {
			return 'map-signal0';
		} elseif ($signala >= 13 && $signala <= 19) {
			return 'map-signal2';
		} elseif ($signala >= 20 && $signala < $signalbadstart) {
			return 'map-signal3';
		} elseif ($signala >= $signalbadstart && $signala <= $signalbadend) {
			return 'map-signal4';
		} else {
			return 'map-signal4';
		}
	}
    public function map_onu_signal($onu) {
		$signal = (string)($onu['ont']['rx'] ?? '0');
		$signalClass = $this->signal_class($signal);
		$signalJs = json_encode($signal, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$classJs = json_encode($signalClass, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		return "var onu_{$onu['ont']['idonu']} = L.marker([{$onu['lan']}, {$onu['lon']}], {icon: getMapSignalIcon({$signalJs}, {$classJs})})";
	}
    public function map_onu($onu) {
		$mapont = '';
		$icon_map = '';
		if($onu['ont']['status'] == 1){
			$icon_map = $this->map_onu_signal($onu);
		}else{
			$icon_map = $this->onu_status_offline_icon($onu);
		}
		if(isset($icon_map)){
		$map_tag = '';

		$name_ont = (!empty($onu['datatemponu']['name']) ? "<b style=\'color:#d100ff;\'>" . preg_replace('/[^a-zA-Zа-яА-ЯёЁіІїЇєЄґҐ0-9 ]/u', '', $onu['datatemponu']['name']) . "</b>" : "");
		$tag_ont  = (!empty($onu['datatemponu']['tag'])  ? "<b style=\'color:#006dff;\'>"  . preg_replace('/[^a-zA-Zа-яА-ЯёЁіІїЇєЄґҐ0-9 ]/u', '', $onu['datatemponu']['tag'])  . "</b>" : "");

		if (!empty($name_ont) || !empty($tag_ont)) {
			$map_tag .= "<br>";
			if (!empty($name_ont)) {
				$map_tag .= $name_ont;
			}
			if (!empty($tag_ont)) {
				if (!empty($name_ont)) {
					$map_tag .= "<br>";
				}
				$map_tag .= $tag_ont;
			}
		}

		$popupContent = "<div class=\"div-l\"><a href=\"/?do=onu&id={$onu['ont']['idonu']}\">{$onu['ont']['mac']}{$onu['ont']['sn']}</a><br>";
        $popupContent .= "{$onu['ont']['type']} {$onu['ont']['inface']}<br><b>RX ONU:</b> " . ($onu['ont']['status'] == 1 ? $onu['ont']['rx'] : 0) . " dbm<br><b>{$this->lang['distance']}:</b> {$onu['ont']['dist']}";
        $popupContent .= (isset($onu['ont']['status']) && $onu['ont']['status'] == 1 ? '<br><b>' . $this->lang['online'] . ':</b> ' . aftertime($onu['ont']['online']) : '<br><b>' . $this->lang['offline'] . ':</b> ' . aftertime($onu['ont']['offline'])).$map_tag . "</div>";
		$popupContentJson = json_encode($popupContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$markerVar = 'onu_' . (int)$onu['ont']['idonu'];
		$mapont .= $icon_map . ";\n";
		$mapont .= "{$markerVar}.options.pmonPopupHtml = {$popupContentJson};\n";
		$mapont .= "{$markerVar}.on('click', function(){if(!this._pmonPopupBound){this.bindPopup(this.options.pmonPopupHtml);this._pmonPopupBound=true;}this.openPopup();});\n";
		$mapont .= "{$markerVar}.addTo((window.onuLayer && typeof window.onuLayer.addLayer === 'function') ? window.onuLayer : map);\n";
		}
		return $mapont;
	}
    public function map_element($onu) {
        $marker = '';
        if (isset($onu['lan']) && isset($onu['lon'])) {

        }
        return $marker;
    }
}
?>
