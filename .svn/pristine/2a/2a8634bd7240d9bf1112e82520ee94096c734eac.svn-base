<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$integration = array(
	'abills_url'=>'2222',
	'abillis_import_street'=>true,
	'abillis_import_builds'=>true,
	'abillis_key'=>'222'
);
global $integration;

function curl_api_abillis($url, $key, $post=''){
	$temp_file = ROOT_DIR . '/export/cache/temp_abills.txt';
	if (file_exists($temp_file)) {
		$fs = file_get_contents($temp_file);
	}
	$fs = $fs.PHP_EOL.$url.' - '.$key;
	file_put_contents($temp_file,$fs);
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL,$url); 
    curl_setopt($ch, CURLOPT_FAILONERROR, 0); 
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); 
    curl_setopt($ch, CURLOPT_HEADER, 0);
    if (is_array($post)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER,
        array('KEY: '.$key)
    );
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
function import_street_abills($data) {
    global $db, $integration;
    $now = date('Y-m-d H:i:s');    
    $get_pmon_street = $db->SimpleWhile("SELECT * FROM location_street");    
    $existing_streets = [];
    if (isset($get_pmon_street) && count($get_pmon_street) > 0) {
        foreach ($get_pmon_street as $street_pmon) {
            $existing_streets[$street_pmon['billing_streetid']] = $street_pmon;
        }
    }    
    if (isset($data['street']) && count($data['street']) > 0) {
        foreach ($data['street'] as $abills_street) {
            if (isset($abills_street['streetName']) && !isset($existing_streets[$abills_street['streetId']])) {
                import_location_street_insert($abills_street);
            }
        }
    }
}
function get_data_house_abills($id) {
    global $integration, $confPMon, $cacheManager;
    $expiration = 300;
    $cacheKey = md5("pmon_abills_house_" . $id);
    $url = $integration['abills_url'].'/api.cgi/builds?streetId='.$id;
    if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
        $cachedResult = $cacheManager->get($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }
    }
    $tmp_house = curl_api_abillis($url, $integration['abillis_key']);
    if ($tmp_house !== FALSE) {
        $house = json_decode($tmp_house, true);
        if (isset($integration['abillis_import_street']) && $integration['abillis_import_street'] == true) {
            if(isset($house)){
				$data = array(
					'streetid' => $id,
					'builds' => $house
				);
				import_builds_abills($data);
			}
        }
        if (isset($house)) {
            if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
                $cacheManager->set($cacheKey, $house, $expiration);
            }
            return $house;
        }
    }
    return false;
}
function import_location_builds_insert($data) {
    global $db, $integration;
    $now = date('Y-m-d H:i:s');    
    $name = Clean::text($data['number']);
	if (isset($name) && preg_match('/[a-zA-Zа-яА-ЯёЁїЇєЄіІґҐ0-9]/u', $name)) {
		$sql ="INSERT INTO `location_build` (`name`, `billing_streetid`, `billing_buildid`, `added`) VALUES (
		'{$name}', '{$data['streetId']}', '{$data['id']}', '{$now}');";
		 $db->query($sql);
	}
}
function import_location_street_insert($abills_street) {
    global $db, $integration;
    $now = date('Y-m-d H:i:s');    
    $name = Clean::text($abills_street['streetName']);
    if (isset($name) && preg_match('/[a-zA-Zа-яА-ЯёЁїЇєЄіІґҐ]/u', $name)) {
        $sql = "INSERT INTO location_street (`name`, `locationid`, `billing_streetid`, `billing_buildcount`, `billing_districtid`, `billing_type`, `added`) 
                VALUES ('{$name}', '{$abills_street['districtId']}', '{$abills_street['streetId']}', '{$abills_street['buildCount']}', '{$abills_street['districtId']}', '{$abills_street['type']}', '{$now}')";
        $db->query($sql);
    }
}
function rechecker_builds_abills() {
	global $db, $integration;
	$tmp_street = $db->SimpleWhile("SELECT * FROM location_build");   
    if (isset($tmp_street) && count($tmp_street) > 0) {
		foreach ($tmp_street as $street) {
			$db->query("UPDATE location_build SET streetid = '{$street['id']}' WHERE billing_streetid  = '{$street['billing_streetid']}'");
		}
	}		
}
function import_builds_abills($data) {
	global $db, $integration;
	$get_pmon_builds = $db->SimpleWhile("SELECT * FROM location_build");    
    $existing_builds_temp = [];
    if (isset($get_pmon_builds) && count($get_pmon_builds) > 0) {
        foreach ($get_pmon_builds as $street_builds) {
            $existing_builds_temp[$street_builds['billing_buildid']] = $street_builds;
        }
    } 	
	if (isset($data['builds']) && count($data['builds']) > 0) {
        foreach ($data['builds'] as $abills_builds) {
            if (isset($abills_builds['number'])  && !isset($existing_builds_temp[$abills_builds['id']])) {
				import_location_builds_insert($abills_builds);
            }
        }
    }
	rechecker_builds_abills();
}
function get_data_street_abills() {
    global $confPMon, $cacheManager,$integration;
    $expiration = 300;
    $cacheKey = md5("pmon_abills_streets");
    $url = $integration['abills_url'].'/api.cgi/streets?streetName=true&pageRows=900';
    if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
        $cachedResult = $cacheManager->get($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }
    }
    $tmp_districts = curl_api_abillis($url, $integration['abillis_key']);    
    if ($tmp_districts !== FALSE) {
        $streets = json_decode($tmp_districts, true);        
        if (isset($integration['abillis_import_street']) && $integration['abillis_import_street'] == true) {
            $data = array(
                'street' => $streets
            );
            import_street_abills($data);
        }
        if (isset($streets)) {
            if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
                $cacheManager->set($cacheKey, $streets, $expiration);
            }
            return $streets;
        }
    }		
    return false;
}
function get_data_districts_abills($integration) {
    global $confPMon, $cacheManager;
    $cacheKey = md5("abills_districts");
    $expiration = 300;
    if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
        $cachedResult = $cacheManager->get($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }
    }
    $url = $integration['abills_url'].'/api.cgi/districts';
    $tmp_districts = curl_api_abillis($url, $integration['abillis_key']);    
    if ($tmp_districts !== FALSE) {
        $districts = json_decode($tmp_districts, true);
        if (isset($districts)) {
            if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
                $cacheManager->set($cacheKey, $districts, $expiration);
            }
            return $districts;
        }
    }
    return false;
}

function pmon_get_data_user_uid($uid,$usr) {
	global $db, $integration;
$sql = "SELECT billing_usr.* , billing_usr.uid as usr_uid, billing_usr.deviceid as olt_id, onus.*, onus.status as onu_status, onus.reason as onu_reason, onus.added as onu_added, onus.mac as onu_mac FROM billing_usr 
LEFT JOIN onus ON (onus.idonu = billing_usr.onuid) WHERE billing_usr.uid = {$uid} LIMIT 1";
$get_usr_database = $db->Simple($sql);
return $get_usr_database;
}
function get_data_nomer_abills($integration, $sender) {
    global $confPMon, $cacheManager;
    $select = '';
    if (isset($sender['houseid']) && $sender['houseid'] > 0) {
        $select .= '&id=' . $sender['houseid'];
    }    
    if (isset($sender['streetid']) && $sender['streetid'] > 0) {
        $select .= '&streetId=' . $sender['streetid'];
    }
    $cacheKey = md5("abills_nomer_" . $select);
    $expiration = 300;
    if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
        $cachedResult = $cacheManager->get($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }
    }
    $url = $integration['abills_url'].'/api.cgi/builds?users=true' . $select;
    $tmp_districts = curl_api_abillis($url, $integration['abillis_key']);    
    if ($tmp_districts !== FALSE) {
        $temp = json_decode($tmp_districts, true);
        if (isset($temp)) {
            if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
                $cacheManager->set($cacheKey, $temp, $expiration);
            }
            return $temp;
        }
    }
    return false;
}
function get_data_user_uid_abills($sender) {
    global $integration, $confPMon, $cacheManager;
    if (isset($sender['uid']) && $sender['uid'] > 0) {
        $cacheKey = md5("abills_user_pi_" . $sender['uid']);
        $expiration = 300;
        if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
            $cachedResult = $cacheManager->get($cacheKey);
            if ($cachedResult !== null) {
                return $cachedResult;
            }
        }
        $url = $integration['abills_url'].'/api.cgi/users/'.$sender['uid'].'/pi';
        $tmp_usr = curl_api_abillis($url, $integration['abillis_key']);
        if ($tmp_usr !== FALSE) {
            $usr = json_decode($tmp_usr, true);
            if (isset($usr)) {
                if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
                    $cacheManager->set($cacheKey, $usr, $expiration);
                }
                return $usr;
            }
        }
    }
    return false;    
}
function get_full_data_user_uid_abills($sender) {
    global $integration, $confPMon, $cacheManager;
    if (isset($sender['uid']) && $sender['uid'] > 0) {
        $cacheKey = md5("abills_user_" . $sender['uid']);
        $expiration = 300;
        if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
            $cachedResult = $cacheManager->get($cacheKey);
            if ($cachedResult !== null) {
                return $cachedResult;
            }
        }
        $url = $integration['abills_url'].'/api.cgi/users/'.$sender['uid'].'/internet';
        $tmp_usr = curl_api_abillis($url, $integration['abillis_key']);        
        if ($tmp_usr !== FALSE) {
            $usr = json_decode($tmp_usr, true);
            if (isset($usr)) {
                if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
                    $cacheManager->set($cacheKey, $usr, $expiration);
                }
                return $usr;
            }
        }
    }
    return false;    
}
function get_data_mac_onu_abills($maconu) {
	global $integration;
    $url = $integration['abills_url'].'/api.cgi/users/internet/all?cpeMac='.$maconu;
    $crez = curl_api_abillis($url,$keyapi);
    if ($crez !== FALSE) {
	$ret = array();
	$crez = json_decode($crez);
	if (isset($crez[0]->uid)) {
	    $uid = $crez[0]->uid;
	    $url = $integration['abills_url'].'/api.cgi/users/'.$uid;
	    $aret['uid'] = $uid;
	    $crez = curl_api_abillis($url,$integration['abillis_key']);
	    if ($crez !== FALSE) {
		$crez = json_decode($crez);
		$aret['gName'] = $crez->gName;
		$aret['login'] = $crez->login;
		$url = $integration['abills_url'].'/api.cgi/users/'.$uid.'/pi';
		$crez = curl_api_abillis($url,$keyapi);
		if ($crez !== FALSE) {
		    $crez = json_decode($crez);
		    $aret['addressFull'] = $crez->addressFull;
		    $aret['addressFullLocation'] = $crez->addressFullLocation;
		    $aret['addressDistrict'] = $crez->addressDistrict;
		    $aret['addressDistrictFull'] = $crez->addressDistrictFull;
		    $aret['addressStreet'] = $crez->addressStreet;
		    $aret['addressBuild'] = $crez->addressBuild;
		    $aret['addressFlat'] = $crez->addressFlat;
		    $aret['fio'] = $crez->fio;
		} else {
		    $aret = false;
		}
	    } else {
		$aret = false;
	    }
	} else {
	$aret = false;
	}
    } else {
	$aret = false;
    }
    return $aret;
}
function get_test_abills($sender) {
	global $integration;
	if(isset($sender['uid']) && $sender['uid'] > 0){
		$url = $integration['abills_url'].'/api.cgi/equipment/'.$sender['uid'].'';
		$tmp_usr = curl_api_abillis($url, $integration['abillis_key']);    
		if ($tmp_usr !== FALSE) {
			$usr = array();
			$usr = json_decode($tmp_usr, true);
			if (isset($usr)) {
				return $usr;
			}
		}
	}
	return false;	
}
?>
