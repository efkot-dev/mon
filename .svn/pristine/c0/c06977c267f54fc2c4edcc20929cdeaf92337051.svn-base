<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
require ENGINE_DIR.'functions/pon.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$get = isset($_POST['get']) ? Clean::text($_POST['get']): null;
$city = isset($_POST['city']) ? Clean::int($_POST['city']): null;
$unit = isset($_POST['unit']) ? Clean::text($_POST['unit']): null;
$location = isset($_POST['location']) ? Clean::text($_POST['location']): null;
$lan = isset($_POST['lan']) ? Clean::text($_POST['lan']): null;
$lon = isset($_POST['lon']) ? Clean::text($_POST['lon']): null;
$listlocation = '';
switch($get){
	case 'info': 
		$list_fiber = get_list_kabel();
		$wheretypes = '';
		$fiberconnect ='';
		$ponelement = $db->Fast('ponelement','*',['id'=>$id]);
		$opis_element = '<div id="opis_element_'.$ponelement['id'].'">';
		if(isset($ponelement['description'])){
			$opis_element .= '<div class="opis_pon_element">'.$ponelement['description'].'</div>';
		}
		$opis_element .= '</div>';
		if(!empty($ponelement['id'])){
			$sqlponelement = $db->SimpleWhile("SELECT * FROM fibers WHERE conn1 = ".$ponelement['id']." OR conn2 = ".$ponelement['id']."");
			$fiberconnect .='<div class="map-list-fiber" id="relust_ponbox_ajax_'.$ponelement['id'].'">';
			if(isset($sqlponelement) && count($sqlponelement)>0){				
				foreach($sqlponelement as $fiber){
					if($fiber['conn1']==$id){
						$wheretypes = $fiber['conn2'];
					}elseif($fiber['conn2']==$id){
						$wheretypes = $fiber['conn1'];
					}else{
						
					}
					if(isset($wheretypes) && $wheretypes>0){
						$connponelement = $db->Fast('ponelement','*',['id'=>$wheretypes]);
						if(!empty($connponelement['id'])){
							$fiberconnect .='<span class="l"><img class="i" src="../style/ponmap/map-cable.png"><span class="n">КВО['.$list_fiber[$fiber['kabel']]['volokon'].']<span class="c"><a href="/?do=fiber&act=view&id='.$connponelement['id'].'">'.$connponelement['name'].'<img src="../style/img/link.png"></a></span></span></span>';
						}
					}				
				}
			}
			$fiberconnect .= '<div id="relust_onu_'.$ponelement['id'].'" class="w100map">'.list_pon_element_map(['pontree'=>$ponelement['tree'],'ponelement'=>$ponelement['id']]).'</div>';
			$fiberconnect .='</div>';
		echo "<div class=\"window-map\" id=\"connectfiber\"><h2><a href=\"/?do=fiber&act=view&id=" . $ponelement['id'] . "\">" . $ponelement['name'] . "<img src=\"../style/img/link.png\"></a></h2>
		".$opis_element."
		".$fiberconnect."<div class=\"key-map\">
		<div class=\"pan\" onclick=\"connectfiber(" . $ponelement['id'] . ");\" id=\"keyconnectfiber\"><img src=\"../style/ponmap/parallel.png\" title=\"Підключити оптику\"></div>
		<div class=\"pan\" onclick=\"opisfiber(" . $ponelement['id'] . ");\" id=\"keydescr\"><img src=\"../style/ponmap/edit.png\" title=\"Додати опис\"></div>
		<div class=\"pan\" onclick=\"connectonu(" . $ponelement['id'] . ");\" id=\"keyonu\"><img src=\"../style/ponmap/addonu.png\" title=\"Додати ONU\"></div>
		
		</div></div>";
		}
	break;		
	case 'spliterview':
        if ($id > 0) {
			$sqlspliters = $db->Simple("SELECT * FROM spliters WHERE id = " . $id. " LIMIT 1");
			echo'soon';
		}
		break;		
	case 'connectpon':	
        $onuid = isset($_POST['onuid']) ? Clean::int($_POST['onuid']) : null;
        if ($onuid > 0) {
            $sqllocation = getListUnit();
            if (is_array($sqllocation)) {
                $listlocation = '';
                foreach ($sqllocation as $loc) {
                    $listlocation .= '<option value="' . $loc['id'] . '">' . $loc['name'] . '</option>';
                }
                $getlocation = '<select class="select" name="id" id="id"><option value="0"></option>' . $listlocation . '</select>';
            }
            echo '<input type="hidden" name="onuid" id="onuid" value="' . $onuid . '">';
            echo '<label class="lab"><b>Мережа</b>' . $getlocation . '</label>';
            echo '<div id="getboxlocation"></div>';
            ?>
            <script>
                $('#id').on('change', function() {
                    var id = $(this).val();
                    $.post('/ajax/fiber.php', { get: 'selectunitpon', id: id }, function(response) {
                        $('#getboxlocation').html(response);
                    }, 'html');
                });
            </script>
            <?php
        }
        die;
        break;

    case 'connectpononus':	
        $id = Clean::int($_POST['id'] ?? null);
        $onuid = Clean::int($_POST['onuid'] ?? null);
        $gettypes = Clean::int($_POST['gettypes'] ?? null);
        $gettree = Clean::int($_POST['gettree'] ?? null);
        if ($onuid > 0 && $gettree > 0 && $gettypes > 0 && $id > 0) {
			$dataonu = $db->Fast('onus','*',['idonu'=>$onuid]);
			if(!empty($dataonu['inface'])){
				$onukey = (!empty($dataonu['mac'])?$dataonu['mac']:(!empty($dataonu['sn'])?$dataonu['sn']:null));
				if(isset($onukey)){
					$getonu = $db->Fast('onusdata','*',['onukey' => $onukey]);
					if(!empty($getonu['id'])){
						$db->SQLupdate('onusdata',['pontree'=>$gettree,'ponelement'=>$gettypes],['id'=>$getonu['id']]);
					}else{
						$db->SQLinsert('onusdata',['onukey'=>$onukey,'pontree'=>$gettree,'ponelement'=>$gettypes]);
					}
					if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
						$cacheManager->delete("onus_data_".$onukey);
					}					
				}
			}
        }
		die;
        break;

    case 'selectelementpon':		
        $gettree = Clean::text($_POST['gettree'] ?? null);
        if ($gettree > 0) {
            $sqlponelement = $db->Multi('ponelement', '*', ['tree' => $gettree]);
            if (count($sqlponelement) > 0) {
                echo '<label class="lab"><b>Елемент</b><select class="select" name="gettypes" id="gettypes"><option value="0"></option>';
                foreach ($sqlponelement as $pele) {
                    if ($pele['id'] !== $pontree['id']) {
                        echo '<option value="' . $pele['id'] . '">' . $pele['name'] . '</option>';
                    }
                }
                echo '</select></label>';
                echo '<br><span class="connect_fiber_btn" onclick="confirmonupon(\''.$lang['fiber_add_conn'].'\');">Підключити</span>';
            }
        }			
        break;

    case 'selectunitpon':	
        $id = Clean::text($_POST['id'] ?? null);
        if ($id > 0) {
            $sqlpontree = $db->Multi('pontree', '*', ['unit_id' => $id]);
            if (count($sqlpontree) > 0) {
                echo '<label class="lab"><b>Дерево</b><select class="select" name="gettree" id="gettree"><option value="0"></option>';
                foreach ($sqlpontree as $tree) {
                    echo '<option value="' . $tree['id'] . '">' . $tree['name'] . '</option>';
                }
                echo '</select></label>';
                echo '<div id="getboxpon"></div>';
                ?>
                <script>
                    $('#gettree').on('change', function() {
                        var gettree = $(this).val();
                        $.post('/ajax/fiber.php', { get: 'selectelementpon', gettree: gettree }, function(response) {
                            $('#getboxpon').html(response);
                        }, 'html');
                    });
                </script>
                <?php
                die;
            }	
        }
        break;		
	case 'ration':		
		$sql = "SELECT DISTINCT od.onukey, o.rx, o.idonu
				FROM onusdata od
				JOIN onus o ON od.onukey = o.mac OR od.onukey = o.sn
					WHERE od.pontree = '{$id}' AND o.status = 1;";
		$sqlonu = $db->SimpleWhile($sql);
		if(isset($sqlonu) && count($sqlonu)>0){	
			$badSignalCount = 0;		
			foreach($sqlonu as $ont){
				$signalValue = intval($ont['rx']);
				if (abs($signalValue) >= 26 && abs($signalValue) <= 40) {
					$badSignalCount++;
				}
			}
			$totalSignals = count($sqlonu);
		}										
		if (isset($totalSignals) && $totalSignals > 0) {
			$percentageBadSignals = ($badSignalCount / $totalSignals) * 100;
			$width = round($percentageBadSignals, 2);
			if ($width < 10) {
				$color = 'x10';
			} elseif ($width >= 10 && $width < 20) {
				$color = 'x20';
			} elseif ($width >= 20 && $width < 99) {
				$color = 'x30';
			} else {
				$color = '';
			}
			echo '<div class="load_bar"><div class="'.$color.'" style="width:'.$width.'%;"></div></div>';
		} else {
			
		}	
	break;		
	case 'connectonu':		
		echo'<input type="hidden" id="ponelement" name="ponelement" value="'.$id.'">';
		?><label class="labspon"><input type="text" class="input1" id="onu" name="onu" required autocomplete="off"><input class="jsadded" type="submit" value="<?=$lang['add'];?>" ></label><div id="searchonu"></div><script>
		$(document).ready(function() {
			$('#onu').on('input', function() {
				var searchText = $(this).val();
				$.ajax({
					method: 'POST',
					url: root + 'ajax/fiber.php',
					data: {get: 'getonu',onu: searchText},
					success: function(response) {
						$('#searchonu').html(response);
					}
				});
			});
			$(document).on('click', '.jsadded', function(e) {
				var onu = $('#onu').val();
				var ponelement = $('#ponelement').val();
				$("#opis_element_" + ponelement).hide();
				$.ajax({
					method: 'POST',
					url: root + 'ajax/fiber.php',
					data: {
						get:'saveonupon',onu:onu,ponelement:ponelement
					},
					success: function(response) {
						$('#relust_onu_' + ponelement).html(response);
					}
				});
			});
			$(document).on('click', '.select-onu', function(e) {
				$(".jsadded").show();
				e.preventDefault();
				var id = $(this).data('id');
				var onuField = $('#onu');
				onuField.val(id);
				$('#searchonu').hide();
			});
		});
		</script>
		<?php
		die;
	break;		
	case 'saveonupon':		
		$id = intval(isset($_POST['ponelement']) ? Clean::int($_POST['ponelement']): null);	
		$onukey = isset($_POST['onu']) ? Clean::text($_POST['onu']): null;
		if(isset($id) && $id>0){
			$ponelement = $db->Fast('ponelement','*',['id'=>$id]);
			if(isset($ponelement['id']) && isset($onukey)){
				$getonu = $db->Fast('onusdata','*',['onukey' => $onukey]);
				if(!empty($getonu['id'])){
					$db->SQLupdate('onusdata',['pontree'=>$ponelement['tree'],'ponelement'=>$ponelement['id']],['id'=>$getonu['id']]);
				}else{
					$db->SQLinsert('onusdata',['onukey'=>$onukey,'pontree'=>$ponelement['tree'],'ponelement'=>$ponelement['id']]);
				}
			}	
			$data = array('pontree'=>$ponelement['tree'],'ponelement'=>$ponelement['id']);
			echo list_pon_element_map($data);
		}			
		die;	
	break;		
	case 'saveopisfiber':		
		$id = intval(isset($_POST['id']) ? Clean::int($_POST['id']): null);	
		$description = isset($_POST['descr']) ? Clean::text($_POST['descr']): null;
		if(isset($id) && $id>0){
			$ponelement = $db->Fast('ponelement','*',['id'=>$id]);
			if(!empty($ponelement['id']) && isset($description)){
				$db->SQLupdate('ponelement',['description'=>$description],['id'=>$ponelement['id']]);
				echo'<div class="opis_pon_element">'.$description.'</div>';
			}
		}
		die;
	break;		
	case 'detail':		
		$id = intval(isset($_POST['id']) ? Clean::int($_POST['id']): null);	
		if(isset($id) && $id>0){
			$ponunit = $db->Fast('ponunit','*',['id'=>$id]);
			if(!empty($ponunit['id'])){	
				echo "<div class=\"window-map\" id=\"connectfiber\">
				<h2>
					<a href=\"/?do=fiber&act=viewunit&id=" . $ponunit['id'] . "\">" . $ponunit['name'] . "<img src=\"../style/img/link.png\"></a></h2>
					<div class=\"key-map\">
						<div class=\"pan\" onclick=\"connectfiberunit(" . $ponunit['id'] . ");\" id=\"keyconnectfiber\"><img src=\"../style/ponmap/parallel.png\" title=\"Підключити оптику\"></div>		
					</div></div>";
			}
		}
	break;		
	case 'connectfiberunit':	
		$list_fiber = get_list_kabel();
		$id = isset($_POST['id']) ? Clean::text($_POST['id']): null;		
		if(isset($id)){
			if(isset($id)){
				$where['unit_id'] = $id;	
				$ponunit = $db->Fast('ponunit','*',['id'=>$id]);
			}
			if(!empty($ponunit['id'])){
				$get_unit = $ponunit['id'];
			}
			$sqllocation = getListUnit();
			if (is_array($sqllocation)) {
				foreach ($sqllocation as $loc) {
					$isSelected = ($ponunit['id'] == $loc['id'] || $id == $loc['id']) ? 'selected' : '';
					$listlocation .= '<option value="' . $loc['id'] . '" ' . $isSelected . '>' . $loc['name'] . '</option>';
				}
				$getlocation = '<select class="select" name="unit" id="unit">' . $listlocation . '</select>';
			}
			echo'<div id="getboxlocation">';
			echo'<label class="lab"><b>Вузол</b>'.$getlocation.'</label>';
			if($get_unit){
				$sqlpontree = $db->Multi('pontree','*',['unit_id'=>$get_unit]);
				if(isset($sqlpontree) && count($sqlpontree)>0){
					echo'<label class="lab"><b>Дерево</b><select class="select" name="gettree" id="gettree">';
					foreach($sqlpontree as $tree){
						echo'<option value="'.$tree['id'].'" '.($tree['id']==$pontree['tree']?'selected':'').'>'.$tree['name'].'</option>';
					}
					echo'</select></label>';
				}
				echo'<div id="getboxlocations">';
				$sqlponelement = $db->Multi('ponelement','*',['unit_id'=>$get_unit]);
				if(isset($sqlponelement) && count($sqlponelement)>0){
					echo'<label class="lab"><b>Елемент</b><select class="select" name="gettypes" id="gettypes">';
					foreach($sqlponelement as $pele){
						if($pele['id']!==$pontree['id']){
							echo'<option value="'.$pele['id'].'">'.$pele['name'].'</option>';
						}
					}
					echo'</select></label>';
				}
				echo'</div>';
				if(is_array($list_fiber)){
					echo'<label class="lab"><b>ОК</b><select class="select" name="kabel" id="kabel">';
					foreach($list_fiber as $key => $fiber){	
						echo'<option value="'.$key.'">'.$fiber['name'].' ВОЛС-'.$fiber['volokon'].'</option>';
					}
					echo'</select></label>';	
				}	
				echo'</div>';
			}
			echo'<span class="connect_fiber_btn" onclick="connunitfiber('.$id.');">Підключити</span>';	
					?><script>
				$('#unit').on('change', function() {
					var unit = $(this).val();
					var idelement = $(this).val();
					var kabel = $(this).val();
					$.post('/ajax/fiber.php',{get:'selectunit',unit:unit,id:idelement},function(response){
						$('#getboxlocation').html(response);
					},'html');
				});		
				$('#gettree').on('change', function() {
					var gettree = $(this).val();
					var idelement = $(this).val();
					var kabel = $(this).val();
					$.post('/ajax/fiber.php',{get:'selectunittree',gettree:gettree,id:idelement},function(response){
						$('#getboxlocations').html(response);
					},'html');
				});
				</script><?php
		}
	break;	
	case 'selectunit':	
		$list_fiber = get_list_kabel();
		if($unit){
			if($unit){
				$where['unit_id'] = $unit;	
				$ponunit = $db->Fast('ponunit','*',['id'=>$unit]);
			}
			if(!empty($ponunit['id'])){
				$get_location = $ponunit['id'];
			}
			$sqllocation = getListUnit();
			if(is_array($sqllocation)){
				foreach($sqllocation as $loc){
					$listlocation .= '<option value="'.$loc['id'].'" '.($ponunit['id']==$loc['id'] || $unit==$loc['id']?'selected':'').'>'.$loc['name'].'</option>';
				}
				$getlocation = '<select class="select" name="unit" id="unit"><option value="0"></option>'.$listlocation.'</select>';
			}
			echo'<div id="getboxlocation">';
			echo'<label class="lab"><b>Вузол</b>'.$getlocation.'</label>';
			if(isset($get_location) && $get_location>0){
				$sqlpontree = $db->Multi('pontree','*',['unit_id'=>$get_location]);
				if(isset($sqlpontree) && count($sqlpontree)>0){
					echo'<label class="lab"><b>Дерево</b><select class="select" name="gettree" id="gettree">';
					echo'<option value="0"></option>';
					foreach($sqlpontree as $tree){
						echo'<option value="'.$tree['id'].'" '.($tree['id']==$pontree['tree']?'selected':'').'>'.$tree['name'].'</option>';
					}
					echo'</select></label>';
				}
				echo'<div id="getboxlocations">';
				if(!empty($pontree['tree'])){
					$sqlponelement = $db->Multi('ponelement','*',['tree'=>$pontree['tree']]);
					if(isset($sqlponelement) && count($sqlponelement)>0){
						echo'<label class="lab"><b>Елемент</b><select class="select" name="gettypes" id="gettypes">';
						foreach($sqlponelement as $pele){
							if($pele['id']!==$pontree['id']){
								echo'<option value="'.$pele['id'].'">'.$pele['name'].'</option>';
							}
						}
						echo'</select></label>';
					}
				}
				echo'</div>';
				if(is_array($list_fiber)){
					echo'<label class="lab"><b>ОК</b><select class="select" name="kabel" id="kabel">';
					foreach($list_fiber as $key => $fiber){	
						echo'<option value="'.$key.'">'.$fiber['name'].' ВОЛС-'.$fiber['volokon'].'</option>';
					}
					echo'</select></label>';	
				}	
				echo'</div>';
			}			
		}
		?><script>
		$('#unit').on('change', function() {
			var unit = $(this).val();
			var idelement = $(this).val();
			var kabel = $(this).val();
			$.post('/ajax/fiber.php',{get:'selectunit',unit:unit,id:idelement},function(response){
				$('#getboxlocation').html(response);
			},'html');
		});		
		$('#gettree').on('change', function() {
			var gettree = $(this).val();
			var idelement = $(this).val();
			var kabel = $(this).val();
			$.post('/ajax/fiber.php',{get:'selectunittree',gettree:gettree,id:idelement},function(response){
				$('#getboxlocations').html(response);
			},'html');
		});
		</script><?php	
	break;		
	case 'selectunittree':	
		$gettree = intval(isset($_POST['gettree']) ? Clean::int($_POST['gettree']): null);	
		if(isset($gettree) && $gettree>0){
			$sqlponelement = $db->Multi('ponelement','*',['tree'=>$gettree]);
			if(isset($sqlponelement) && count($sqlponelement)>0){
				echo'<label class="lab"><b>Елемент</b><select class="select" name="gettypes" id="gettypes">';
				foreach($sqlponelement as $pele){
					if($pele['id']!==$pontree['id']){
						echo'<option value="'.$pele['id'].'">111'.$pele['name'].'</option>';
					}
				}
				echo'</select></label>';
			}
		}			
	break;		
	case 'opisfiber':	
		$id = intval(isset($_POST['id']) ? Clean::int($_POST['id']): null);	
		if(isset($id) && $id>0){
			$ponelement = $db->Fast('ponelement','*',['id'=>$id]);
			if(!empty($ponelement['id'])){
				echo'<textarea name="descr" id="description_'.$ponelement['id'].'" class="comm">'.$ponelement['description'].'</textarea><br><input type="button" class="btn-comm" value="Зберегти" id="form-comment" onclick="savedescrfiber(' . $ponelement['id'] . ');"><br>';
			}
		}
	break;		
	case 'selectvyzol':		
		if(isset($lon) && isset($lan)){
			$listunit = $db->SimpleWhile("SELECT * FROM ponunit");
			$select = '';
			if(is_array($listunit)){
				foreach($listunit as $key => $units){	
					if(empty($units['types']) || $units['types'] == 0){
						$select .= '<option value="'.$units['id'].'">'.$units['name'].'</option>';
					}
				}
			}
			$lan = truncateAfterDot($lan,5);
			$lon = truncateAfterDot($lon,5);
			echo'<label class="flex_div">';
			echo'<div>Розташування вузла</div>';
			echo'<div><select class="select" name="getunit" id="getunit">';
			echo $select;
			echo'</select></div>';
			echo'<div><input class="jsadd" type="submit" value="'.$lang['addeds'].'" ></div>';
			?>
			<script>
			$(document).ready(function() {
				$(document).on('click', '.jsadd', function(e) {
						var getunit = $('#getunit').val();
						var name = $('#name').val();
						$.ajax({method: 'POST',url:'/?do=fiber',data: {act:'saveunitmap',getunit:getunit,lan:<?=$lan;?>,lon:<?=$lon;?>},success: function(response) {
							$(location).attr('href');location.reload(); 
						}});
					});
				});
			</script>
			<?php
		}	
	break;		
	case 'selecteth':	
		if(isset($unit) && $unit>0){
			$lan = truncateAfterDot($lan,5);
			$lon = truncateAfterDot($lon,5);
			echo'<input type="hidden" id="unit" name="unit" value="'.$unit.'">';
			echo'<label class="flex_div">';
			echo'<div><input style="width:200px;" type="text" class="input1" id="name" name="name" required autocomplete="off"></div>';
			echo'<div><select class="select" name="gettypes" id="gettypes">';
			echo'<option value="1">Свіч</option>';
			echo'<option value="2">Медіаконвертор</option>';
			echo'<option value="3">Клієнт на Eth</option>';
			echo'</select></div>';
			echo'<div><input class="jsadd" type="submit" value="'.$lang['added'].'" ></div>';
			echo'</div>';
			?><script>
			$(document).ready(function() {
				$(document).on('click', '.jsadd', function(e) {
					var types = $('#gettypes').val();
					var name = $('#name').val();
					$.ajax({method: 'POST',url:'/?do=fiber',data: {act:'ethelement',name:name,types:types,unit:<?=$unit;?>,lan:<?=$lan;?>,lon:<?=$lon;?>},success: function(response) {
						$(location).attr('href');location.reload(); 
					}});
				});
			});
		</script>
		<?php
		}	
	break;		
	case 'selectpononu':	
		if(isset($unit) && $unit>0){
			$lan = truncateAfterDot($lan,5);
			$lon = truncateAfterDot($lon,5);
		?>
		<label class="flex_div">	
		<input type="hidden" id="idonu" name="idonu">		
		<div><input style="width:200px;" type="text" class="input1" id="onu" name="onu" required autocomplete="off"></div>
		<div><input class="jsadded" type="submit" value="Додати" ></div>
		</label>
		<div id="searchonu"></div>
		<script>
		$(document).ready(function() {
			$('#onu').on('input', function() {
				var searchText = $(this).val();
				$.ajax({
					method: 'POST',
					url: root + 'ajax/fiber.php',
					data: {get: 'getonu',onu: searchText},
					success: function(response) {
						$('#searchonu').html(response);
					}
				});
			});
			$(document).on('click', '.jsadded', function(e) {
				var idonu = $('#idonu').val();
				$.ajax({
					method: 'POST',
					url: root + '?do=send',
					data: {
						act:'addmapperonu',id: idonu,lan:<?=$lan;?>,lon:<?=$lon;?>
					},
					success: function(response) {
						$(location).attr('href');
						location.reload(); 
					}
				});
			});
			$(document).on('click', '.select-onu', function(e) {
				$(".jsadded").show();
				e.preventDefault();
				var id = $(this).data('id');
				var onuField = $('#onu');
				onuField.val(id);
				
				var idonu = $(this).data('idonu');
				var onuFieldidonu = $('#idonu');
				onuFieldidonu.val(idonu);
				
				$('#searchonu').empty();
			});
		});
		</script>
		<?php
		}
	break;
	case 'saveposition':		
		$left = intval(isset($_POST['left']) ? Clean::text($_POST['left']): null);
		$top = intval(isset($_POST['top']) ? Clean::text($_POST['top']): null);
		$kabel = isset($_POST['kabel']) ? Clean::text($_POST['kabel']): null;
		$intkabel = (int)str_replace('kabel-', '', $kabel);
		if(is_numeric($top) && is_numeric($left) && is_numeric($intkabel)) {
			$position = $db->Fast('kabel_position','*',['typesid'=>$intkabel]);
			if(!empty($position['id'])){
				$db->SQLupdate('kabel_position',['xleft'=>$left,'xtop'=>$top],['id'=>$position['id']]);
			}else{
				$db->SQLinsert('kabel_position',['xleft'=>$left,'xtop'=>$top,'typesid'=>$intkabel]);
			}
		}
	break;		
	case 'getonu':		
		if (isset($_POST['onu'])) {
		$zapros = (isset($_POST["onu"]) ? str_replace(' ','',Clean::text(trim(strip_tags(stripcslashes($_POST["onu"]))))):null);
		$cleanZapros = str_replace('.', '', $zapros);
		if (preg_match('/^[a-fA-F0-9]{12}$/', $cleanZapros)) {
			$zapros = implode(':', str_split($cleanZapros, 2));
		}
		$orderby = " ORDER BY idonu ASC";
		$limit = " LIMIT 10";
		$where_onusdata = "(onusdata.tag LIKE '%".$zapros."%' OR onusdata.name LIKE '%".$zapros."%' OR onusdata.uid LIKE '%".$zapros."%')";
		$where_onus = "(onus.sn LIKE '%".$zapros."%' OR onus.name LIKE '%".$zapros."%' OR onus.mac LIKE '%".$zapros."%')";
		$sqlonus = $db->SimpleWhile("SELECT onusdata.*, onus.* FROM onusdata
			LEFT JOIN onus ON (onus.mac = onusdata.onukey OR onus.sn = onusdata.onukey)
			WHERE $where_onusdata $orderby $limit");
		if (empty($sqlonus)) {
			$sqlonus = $db->SimpleWhile("SELECT * FROM onus WHERE $where_onus $orderby $limit");
		} else {
			$sqlonus_from_onus = $db->SimpleWhile("SELECT * FROM onus WHERE (onus.name LIKE '%".$zapros."%' OR onus.mac LIKE '%".$zapros."%' OR onus.sn LIKE '%".$zapros."%') $orderby $limit");
			$sqlonus = array_merge($sqlonus, $sqlonus_from_onus);
		}
		if(count($sqlonus)>0){
			echo '<ul class="resultpoiskonu">';
			foreach ($sqlonus as $ont) {
				$onukey = (!empty($ont['mac'])?$ont['mac']:(!empty($ont['sn'])?$ont['sn']:null));
				if($onukey!=false){
				echo '<li><a href="#" class="select-onu" data-id="' . $onukey . '" data-idonu="' . $ont['idonu'] . '">';
				echo'<span>'.$ont['inface'].'</span> ' . $onukey . '';
				echo'</a></li>';
				}
			}
			echo '</ul>';
		}else{
			echo'Давай по іншому';
		}
		}
	break;		
	case 'saveonu':		
		$ponelement = intval(isset($_POST['ponelement']) ? Clean::int($_POST['ponelement']): null);	
		$pontree = intval(isset($_POST['pontree']) ? Clean::int($_POST['pontree']): null);	
		$onukey = (isset($_POST['onu']) ? Clean::text($_POST['onu']): null);
		if(isset($ponelement) && isset($pontree) && isset($onukey)){
			$getonu = $db->Fast('onusdata','*',['onukey' => $onukey]);
			if(!empty($getonu['id'])){
				$db->SQLupdate('onusdata',['pontree'=>$pontree,'ponelement'=>$ponelement],['id'=>$getonu['id']]);
			}else{
				$db->SQLinsert('onusdata',['onukey'=>$onukey,'pontree'=>$pontree,'ponelement'=>$ponelement]);
			}
			$data = array('pontree'=>$pontree,'ponelement'=>$ponelement);
			echo list_pon_element($pdo,$data);
			///echo list_pon_element_spliters($ponelement);
			echo "<span class=\"addelement\" onclick=\"addelement(". $ponelement.",1,".$pontree.")\">[додати]</span>";
		}
	break;
	case 'sersignal':			
		$ponelement = intval(isset($_POST['id']) ? Clean::int($_POST['id']): null);	
		$pontree = intval(isset($_POST['tree']) ? Clean::int($_POST['tree']): null);
		$data = array('pontree'=>$pontree,'ponelement'=>$ponelement);
		echo signal_min($data);
		exit;
		break;
	case 'listonu':		
		$ponelement = intval(isset($_POST['id']) ? Clean::int($_POST['id']): null);	
		$pontree = intval(isset($_POST['tree']) ? Clean::int($_POST['tree']): null);	
		$data = array('pontree'=>$pontree,'ponelement'=>$ponelement);
		echo list_pon_element($pdo,$data,$lang);
		#echo list_pon_element_spliters($ponelement);
		echo "<span class=\"addelement\" onclick=\"addelement(". $ponelement.",1,".$pontree.")\">[".$lang['connect']."]</span>";
		exit;
	break;	
	case 'deletspliter':		
		$spliterid = (isset($_POST['spliterid']) ? Clean::int($_POST['spliterid']): null);
		if(isset($spliterid)){
			$db->SQLdelete('spliters',['id' => $spliterid]);
		}
	break;	
	case 'savespliter':		
		$spliterid = (isset($_POST['spliterid']) ? Clean::int($_POST['spliterid']): null);
		$ponelement = (isset($_POST['ponelement']) ? Clean::int($_POST['ponelement']): null);
		if(isset($ponelement) && isset($spliterid) && $spliterid>0){
			$pondata = $db->Fast('ponelement','*',['id'=>$ponelement]);
			$db->SQLinsert('spliters',['ponelementname'=>$pondata['name'],'pontreeid'=>$pondata['tree'],'ponelement'=>$ponelement,'spliterid'=>$spliterid,'userid'=>$USER['id'],'added'=>date('Y-m-d H:i:s')]);
		}
	break;	
	case 'getelement':		
		$id = intval(isset($_POST['id']) ? Clean::int($_POST['id']): null);	
		$tree = intval(isset($_POST['tree']) ? Clean::int($_POST['tree']): null);	
		$getelement = (isset($_POST['getelement']) ? Clean::text($_POST['getelement']): null);	
		if(isset($id) && $getelement=='vidgaluj'){
			echo '<div class="ponelementdevice">'.spliter_list(['type'=>'vidgaluj']).'<label class="labs"><input class="jsadded" type="submit" value="Додати" style="display: inline-block;"></label></div>';?><script>$(document).on('click', '.jsadded', function(e) {	var spliterid = $('#ponbox_element').val();	$.ajax({method: 'POST',url: root + 'ajax/fiber.php',data: {get:'savespliter',spliterid: spliterid,ponelement:<?=$id;?>},success: function(response) {sayHi();}});});</script><?php
		}elseif(isset($id) && $getelement=='dilnuk'){
			echo '<div class="ponelementdevice">'.spliter_list(['type'=>'dilnuk']).'<label class="labs"><input class="jsadded" type="submit" value="Додати" style="display: inline-block;"></label></div>';?><script>$(document).on('click', '.jsadded', function(e) {	var spliterid = $('#ponbox_element').val();	$.ajax({method: 'POST',url: root + 'ajax/fiber.php',data: {get:'savespliter',spliterid: spliterid,ponelement:<?=$id;?>},success: function(response) {sayHi();}});});</script><?php
		}elseif(isset($id) && $getelement=='onu'){
			echo'<input type="hidden" id="ponelement" name="ponelement" value="'.$id.'">
			<input type="hidden" id="pontree" name="pontree" value="'.$tree.'">';
		?>
		<label class="labs">		
		<input style="width:200px;" type="text" class="input1" id="onu" name="onu" required autocomplete="off">
		<input class="jsadded" type="submit" value="Додати" >
		</label>
		<div id="searchonu"></div>
		<script>
		$(document).ready(function() {
			$('#onu').on('input', function() {
				var searchText = $(this).val();
				$.ajax({
					method: 'POST',
					url: root + 'ajax/fiber.php',
					data: {get: 'getonu',onu: searchText},
					success: function(response) {
						$('#searchonu').html(response);
					}
				});
			});
			$(document).on('click', '.jsadded', function(e) {
				var onu = $('#onu').val();
				var ponelement = $('#ponelement').val();
				var pontree = $('#pontree').val();
				$.ajax({
					method: 'POST',
					url: root + 'ajax/fiber.php',
					data: {
						get:'saveonu',onu: onu,ponelement: ponelement,pontree: pontree
					},
					success: function(response) {
						$('.addelement_' + ponelement).html(response);
					}
				});
			});
			$(document).on('click', '.select-onu', function(e) {
				$(".jsadded").show();
				e.preventDefault();
				var id = $(this).data('id'); // Отримайте ідентифікатор з атрибута data-id
				var onuField = $('#onu');
				onuField.val(id);
				$('#searchonu').empty();
			});
		});
		</script>
		<?php
		}
	break;		
	case 'formelemet':	
		$id = intval(isset($_POST['id']) ? Clean::int($_POST['id']) : null);
		$tree = intval(isset($_POST['tree']) ? Clean::int($_POST['tree']) : null);

		if (isset($id) && isset($tree)) {
			echo '<div class="lab">';
			echo '<div class="img-btn" data-element="onu"><img src="../style/img/pon/onu.png" alt="ONU"></div>';
			#echo '<div class="img-btn" data-element="vidgaluj"><img src="../style/img/pon/spliter1.png" alt="Відгалужувач"></div>';
			#echo '<div class="img-btn" data-element="dilnuk"><img src="../style/img/pon/spliter12.png" alt="Дільник"></div>';
			echo '</div>';
			
			echo "<script>
				$('.img-btn').on('click', function() {
					var getelement = $(this).data('element');
					$.post('/ajax/fiber.php', {
						get: 'getelement',
						getelement: getelement,
						id: '$id',
						tree: '$tree'
					}, function(response) {
						$('.addelement_$id').html(response);
					}, 'html');
				});
			</script>";
		}
	break;	
	case 'selectelement':		
		$sqllocation = getListUnit();
		if (is_array($sqllocation)) {
			foreach ($sqllocation as $loc) {
				$listlocation .= '<option value="' . $loc['id'] . '" ' . (isset($unit) && $unit == $loc['id'] ? 'selected' : '') . '>' . $loc['name'] . '</option>';
			}
			$getlocation = '<select class="select" name="unit" id="unit"><option value="0"></option>' . $listlocation . '</select>';
		}
		echo '<label class="lab"><b>Вузол</b>' . $getlocation . '</label>';
		echo '<div id="getboxlocation"></div><input type="hidden" name="lan" id="lan" value="' . truncateAfterDot($lan,5) . '"><input type="hidden" name="lon" id="lon" value="' . truncateAfterDot($lon,5) . '">';
		if (isset($unit) && $unit>0) {
			echo "
				<script>
					$(document).ready(function() {
						$('#unit').on('change', function() {
							var unit = $(this).val();
							$.post('/ajax/fiber.php', { get: 'selectcitytree', unit: unit }, function(response) {
								$('#getboxlocation').html(response);
							}, 'html');
						}).change();
					});
				</script>";
		} else {
			echo "
				<script>
					$(document).ready(function() {
						$('#unit').on('change', function() {
							var unit = $(this).val();
							if (unit !== '') {
								$.post('/ajax/fiber.php', { get: 'selectcitytree', unit: unit }, function(response) {
									$('#getboxlocation').html(response);
								}, 'html');
							}
						});
					});
				</script>";
		}
	break;		
	case 'selectcitytree':
		if(isset($unit) && $unit>0){
			$sqlpontree = $db->Multi('pontree','*',['unit_id'=>$unit]);
			if(count($sqlpontree)){
				echo'<label class="lab"><b>Дерево</b><select class="select" name="gettree" id="gettree">';
				echo'<option value="0"></option>';
				foreach($sqlpontree as $tree){
					echo'<option value="'.$tree['id'].'">'.$tree['name'].'</option>';
				}
				echo'</select></label>';
				echo'<div id="getboxpon"></div>';
				echo"<script>
				$('#gettree').on('change', function() {
					var gettree = $(this).val();
					$.post('/ajax/fiber.php',{get:'selecttreepon',unit:".$unit.",gettree:gettree},function(response){
						$('#getboxpon').html(response);
					},'html');});		
				</script>";	
			}
		}
	break;		
	case 'selecttreepon':	
		$gettree = isset($_POST['gettree']) ? Clean::text($_POST['gettree']): null;		
		if(isset($unit) && $unit>0 && $gettree){
			echo'<label class="lab"><b>Елемент</b>'.getPonElement().'</label>';
			echo'<label class="lab"><input type="text" class="input1" id="name" name="name" required autocomplete="off"></label>';
			echo'<span class="connect_fiber_btn" onclick="mapsaveelement();">Додати</span>';	
		}
	break;		
	case 'selectcity':	
		$list_fiber = get_list_kabel();
		$gettree = isset($_POST['gettree']) ? Clean::text($_POST['gettree']): null;		
		if($gettree && !$unit){
			echo'<div id="getboxlocations">';
			$sqlponelement = $db->Multi('ponelement','*',['tree'=>$gettree]);
			if(isset($sqlponelement) && count($sqlponelement)>0){
				echo'<label class="lab"><b>Елемент</b><select class="select" name="gettypes" id="gettypes">';
				echo'<option value="0"></option>';
				foreach($sqlponelement as $pele){
					echo'<option value="'.$pele['id'].'">'.$pele['name'].'</option>';
				}
				echo'</select></label>';
			}
			echo'</div>';
		}else{
			if($id){
				$where['id'] = $id;
			}
			if($unit){
				$where['unit_id'] = $unit;	
				$ponunit = $db->Fast('ponunit','*',['id'=>$unit]);
			}
			if(!empty($ponunit['id'])){
				$get_location = $ponunit['id'];
			}else{
				$pontree = $db->Fast('ponelement','*',$where);	
				$get_location = $pontree['unit_id'];				
			}
			$sqllocation = getListUnit();
			if(is_array($sqllocation)){
				foreach($sqllocation as $loc){
					$listlocation .= '<option value="'.$loc['id'].'" '.($pontree['unit_id']==$loc['id'] || $unit==$loc['id']?'selected':'').'>'.$loc['name'].'</option>';
				}
				$getlocation = '<select class="select" name="unit" id="unit"><option value="0"></option>'.$listlocation.'</select>';
			}
			echo'<div id="getboxlocation">';
			echo'<label class="lab"><b>Вузол</b>'.$getlocation.'</label>';
			if(isset($get_location) && $get_location>0){
				$sqlpontree = $db->Multi('pontree','*',['unit_id'=>$get_location]);
				if(isset($sqlpontree) && count($sqlpontree)>0){
					echo'<label class="lab"><b>Дерево</b><select class="select" name="gettree" id="gettree">';
					echo'<option value="0"></option>';
					foreach($sqlpontree as $tree){
						echo'<option value="'.$tree['id'].'" '.($tree['id']==$pontree['tree']?'selected':'').'>'.$tree['name'].'</option>';
					}
					echo'</select></label>';
				}
				echo'<div id="getboxlocations">';
				if(!empty($pontree['tree'])){
					$sqlponelement = $db->Multi('ponelement','*',['tree'=>$pontree['tree']]);
					if(isset($sqlponelement) && count($sqlponelement)>0){
						echo'<label class="lab"><b>Елемент</b><select class="select" name="gettypes" id="gettypes">';
						foreach($sqlponelement as $pele){
							if($pele['id']!==$pontree['id']){
								echo'<option value="'.$pele['id'].'">'.$pele['name'].'</option>';
							}
						}
						echo'</select></label>';
					}
				}
				echo'</div>';
				if(is_array($list_fiber)){
					echo'<label class="lab"><b>ОК</b><select class="select" name="kabel" id="kabel">';
					foreach($list_fiber as $key => $fiber){	
						echo'<option value="'.$key.'">'.$fiber['name'].' ВОЛС-'.$fiber['volokon'].'</option>';
					}
					echo'</select></label>';	
				}	
				echo'</div>';
			}			
		}
		?><script>
		$('#unit').on('change', function() {
			var unit = $(this).val();
			var idelement = $(this).val();
			var kabel = $(this).val();
			$.post('/ajax/fiber.php',{get:'selectcity',unit:unit,id:idelement},function(response){
				$('#getboxlocation').html(response);
			},'html');
		});		
		$('#gettree').on('change', function() {
			var gettree = $(this).val();
			var idelement = $(this).val();
			var kabel = $(this).val();
			$.post('/ajax/fiber.php',{get:'selectcity',gettree:gettree,id:idelement},function(response){
				$('#getboxlocations').html(response);
			},'html');
		});
		</script><?php	
	break;		
	case 'connectfiber':
		$list_fiber = get_list_kabel();
		$gettree = isset($_POST['gettree']) ? Clean::text($_POST['gettree']): null;		
		if(isset($gettree) && !$unit){
			echo'<div id="getboxlocations">';
			$sqlponelement = $db->Multi('ponelement','*',['tree'=>$gettree]);
			if(isset($sqlponelement) && count($sqlponelement)>0){
				echo'<label class="lab"><b>Елемент</b><select class="select" name="gettypes" id="gettypes">';
				foreach($sqlponelement as $pele){
					echo'<option value="'.$pele['id'].'">'.$pele['name'].'</option>';
				}
				echo'</select></label>';
			}
			echo'</div>';
		}else{
			if(isset($id)){
				$where['id'] = $id;
			}
			if(isset($unit)){
				$where['unit_id'] = $unit;	
				$ponunit = $db->Fast('ponunit','*',['id'=>$unit]);
			}
			if(!empty($ponunit['id'])){
				$get_location = $ponunit['id'];
			}else{
				$pontree = $db->Fast('ponelement','*',$where);	
				$get_location = $pontree['unit_id'];				
			}
			$sqllocation = getListUnit();
				if (is_array($sqllocation)) {
					foreach ($sqllocation as $loc) {
						$isSelected = ($pontree['unit_id'] == $loc['id'] || $unit == $loc['id']) ? 'selected' : '';
						$listlocation .= '<option value="' . $loc['id'] . '" ' . $isSelected . '>' . $loc['name'] . '</option>';
					}

					$getlocation = '<select class="select" name="unit" id="unit">' . $listlocation . '</select>';
				}
			echo'<div id="getboxlocation">';
			echo'<label class="lab"><b>Вузол</b>'.$getlocation.'</label>';
			if($get_location){
				$sqlpontree = $db->Multi('pontree','*',['unit_id'=>$get_location]);
				if(isset($sqlpontree) && count($sqlpontree)>0){
					echo'<label class="lab"><b>Дерево</b><select class="select" name="gettree" id="gettree">';
					foreach($sqlpontree as $tree){
						echo'<option value="'.$tree['id'].'" '.($tree['id']==$pontree['tree']?'selected':'').'>'.$tree['name'].'</option>';
					}
					echo'</select></label>';
				}
				echo'<div id="getboxlocations">';
				$sqlponelement = $db->Multi('ponelement','*',['tree'=>$pontree['tree']]);
				if(isset($sqlponelement) && count($sqlponelement)>0){
					echo'<label class="lab"><b>Елемент</b><select class="select" name="gettypes" id="gettypes">';
					foreach($sqlponelement as $pele){
						if($pele['id']!==$pontree['id']){
							echo'<option value="'.$pele['id'].'">'.$pele['name'].'</option>';
						}
					}
					echo'</select></label>';
				}
				echo'</div>';
				if(is_array($list_fiber)){
					echo'<label class="lab"><b>ОК</b><select class="select" name="kabel" id="kabel">';
					foreach($list_fiber as $key => $fiber){	
						echo'<option value="'.$key.'">'.$fiber['name'].' ВОЛС-'.$fiber['volokon'].'</option>';
					}
					echo'</select></label>';	
				}	
				echo'</div>';
			}			
		}
		echo'<input type="hidden" name="idelement" id="idelement" value="'.$id.'">';
		echo'<span class="connect_fiber_btn" onclick="sendconnectfibber('.$id.');">Підключити</span>';	
		?><script>
		$('#unit').on('change', function() {
			var unit = $(this).val();
			var idelement = $(this).val();
			var kabel = $(this).val();
			$.post('/ajax/fiber.php',{get:'selectcity',unit:unit,id:idelement},function(response){
				$('#getboxlocation').html(response);
			},'html');
		});		
		$('#gettree').on('change', function() {
			var gettree = $(this).val();
			var idelement = $(this).val();
			var kabel = $(this).val();
			$.post('/ajax/fiber.php',{get:'selectcity',gettree:gettree,id:idelement},function(response){
				$('#getboxlocations').html(response);
			},'html');
		});
		</script><?php	
	break;	
	default:	
		$unit = isset($_GET['unit']) ? Clean::int($_GET['unit']): null;
		echo'<div id="map-menu"><div class="element-mapper">';
		echo'<a href="#" class="element-menu" onclick="selectpononu('.$lan.','.$lon.','.$unit.');">
		<img src="../style/img/onu.png">
		<span>Onu</span>
		
		</a>';
		/*
				echo'<a href="#" class="element-menu" onclick="selectponelement('.$lan.','.$lon.','.$unit.');">
		<img src="../style/img/sfperr.png">
		<span>Пон</span>
		</a>';
		echo'<a href="#" class="element-menu" onclick="selecteth('.$lan.','.$lon.','.$unit.');">
		<img src="../style/img/box.png">
		<span>Eth</span>
		
		</a>';		

		echo'<a href="#" class="element-menu" onclick="selectvyzol('.$lan.','.$lon.','.$unit.');">
		<img src="../style/img/box.png">
		<span>Вузол</span>
		
		</a>';*/
		echo'</div></div>';
}
die;
?>
