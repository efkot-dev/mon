<?php
if (!defined('PONMONITOR')){
    die('Hacking attempt!');
}
$invoiceView = '';
switch($act){
	case 'add':
		$metatags = [
			'title' => 'Нова накладна',
			'description' => 'Нова накладна',
			'page' => 'calc'
		];
		$invoiceView .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
			<a class="brmhref" href="/?do=calc"><i class="fi fi-rr-angle-left"></i>Проектування</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Нова накладна</span>
		</div>
		';
		$invoiceView .= '
		<table id="formTable" cellspacing="0" cellpadding="0">
		<tr><td class="forminputcalc">
		<div class="body-td">
			<label for="invoice_name">Назва накладної:</label>
			<input type="text" id="invoice_name" name="invoice_name" required>
			<label for="invoice_description">Опис:</label>
			<textarea id="invoice_description" name="invoice_description"></textarea>
			<label for="markup">Накрутка (%):</label>
			<input class="pole_warning" type="number" id="markup" name="markup" value="0">
		</div>		
		<td>
		<div class="body-td-s">
			<div class="list-row-div">Загальна сума<div id="pole_custom_total">0 грн</div></div>
			<div class="list-row-div" style="color:#da15d7;">Закупівля<div id="pole_total_purchase">0 грн</div></div>
			<div class="list-row-div" style="color:tomato;">Прихід<div id="pole_total_pruxid">0 грн</div></div>
			<div class="list-row-div" style="color:grey;">Траспортні витрати <div id="pole_driver">0 км</div></div>
			<div class="list-row-div" style="color:green;">Оплата праці<div id="pole_work">0 грн</div></div>
		</td></tr></table>
		<table id="formTable" cellspacing="0" cellpadding="0"><tr><td>
		<table id="invoiceTable" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th class="col-no">№</th>
					<th class="col-product-name">Назва товару</th>
					<th class="col-type">Тип</th>
					<th class="col-purchase-price">Ціна зак.</th>
					<th class="col-quantity">К-ть</th>
					<th class="col-total-purchase">Сума зак.</th>
					<th class="col-price-with-markup">Ціна</th>
					<th class="col-total-with-markup">Сума</th>
					<th class="col-custom-price">Ціна руч.</th>
					<th class="col-custom-total">Заг</th>
					<th class="col-actions"></th>
				</tr>
			</thead>
			<tbody id="invoiceBody">
				
			</tbody>
		</table>
		<div class="flex-right mtop10">
		<button id="addRow">Додати рядок</button>
		<button id="saveInvoice">Зберегти накладну</button>	
		</div>
		</td></tr></table>';
		break;	
	case 'accountant': 			
	$metatags = [
			'title' => 'Перегляд накладної',
			'description' => 'Перегляд накладної',
			'page' => 'viewcalc'
		];		
    $invoiceId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $invoice = $db->Simple("SELECT * FROM shop_invoices WHERE id = $invoiceId LIMIT 1");
    $invoiceItems = $db->SimpleWhile("SELECT ii.*, p.name 
                                      FROM shop_invoice_items ii 
                                      JOIN shop_products p ON ii.product_id = p.id 
                                      WHERE ii.invoice_id = $invoiceId");
    $invoiceView = '<h1>Перегляд Накладної #' . $invoiceId . '</h1>
                    <p><strong>Назва Накладної:</strong> ' . htmlspecialchars($invoice['name']) . '</p>
                    <p><strong>Опис:</strong> ' . htmlspecialchars($invoice['description']) . '</p>
                    <p><strong>Накрутка (%):</strong> ' . htmlspecialchars($invoice['markup_percentage']) . '</p>';
    $invoiceView .= '<table border="1">
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Назва товару</th>
                                <th>Кількість</th>
                                <th>Ціна з накруткою</th>
                                <th>Сума з накруткою</th>
                                <th>Ручна ціна</th>
                                <th>Сума ручної ціни</th>
                            </tr>
                        </thead>
                        <tbody>';
    
    $totalPurchase = 0;
    $totalPriceWithMarkup = 0;
    $totalCustom = 0;

    foreach ($invoiceItems as $index => $item) {
        $priceWithMarkup = $item['total_price_with_markup'] / $item['quantity'];
        $customPrice = $item['custom_price'] ?? $priceWithMarkup;
        $customTotal = $item['custom_total'] ?? ($customPrice * $item['quantity']);
        
        $totalPurchase += $item['total_purchase'];
        $totalPriceWithMarkup += $item['total_price_with_markup'];
        $totalCustom += $customTotal;
        
        $invoiceView .= '<tr>
                            <td>' . ($index + 1) . '</td>
                            <td>' . htmlspecialchars($item['name']) . '</td>
                            <td>' . intval($item['quantity']) . '</td>
                            <td>' . number_format($priceWithMarkup, 2) . '</td>
                            <td>' . number_format($item['total_price_with_markup'], 2) . '</td>
                            <td>' . number_format($customPrice, 2) . '</td>
                            <td>' . number_format($customTotal, 2) . '</td>
                        </tr>';
    }

    $invoiceView .= '</tbody>
                     <tfoot>
                        <tr>
                            <td colspan="4"><strong>Загальна сума закупки:</strong></td>
                            <td>' . number_format($totalPurchase, 2) . '</td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td colspan="4"><strong>Загальна сума з накруткою:</strong></td>
                            <td>' . number_format($totalPriceWithMarkup, 2) . '</td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td colspan="4"><strong>Загальна сума ручної ціни:</strong></td>
                            <td colspan="2">' . number_format($totalCustom, 2) . '</td>
                        </tr>
                     </tfoot>
                     </table>';	
    break;
	case 'view': 
		$metatags = [
			'title' => 'Перегляд накладної',
			'description' => 'Перегляд накладної',
			'page' => 'viewcalc'
		];	
		$invoiceId = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$invoice = $db->Simple("SELECT * FROM shop_invoices WHERE id = $invoiceId LIMIT 1");
		$invoiceItems = $db->SimpleWhile("SELECT ii.*, p.name, p.types 
            FROM shop_invoice_items ii 
            JOIN shop_products p ON ii.product_id = p.id 
            WHERE ii.invoice_id = $invoiceId");
		$invoiceView .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
			<a class="brmhref" href="/?do=calc"><i class="fi fi-rr-angle-left"></i>Проектування</a>
			<a class="brmhref" href="/?do=calc&act=list"><i class="fi fi-rr-angle-left"></i>Всі накладні</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Перегляд Накладної #' . $invoiceId . '</span>
		</div>
		';
		$invoiceView .= (!empty($invoice['description']) ? '<p><strong>Опис:</strong> ' . $invoice['description'] . '</p>' : '').'';
		$invoiceView .= '
		<table id="myPrint" class="resp-tab list-onu-olt" style="width: auto;"><thead><tr>
			<th class="mob_w10" width="4%">№</th><th >Замовлення: ' . $invoice['name'] . '</th><th width="10%">Кількість</th><th width="10%">Ціна</th><th width="10%">Сума</th>
			</tr>
			</thead><tbody>';
		$totalPurchase = 0;
		$totalPriceWithMarkup = 0;
		$totalCustom = 0;
		foreach ($invoiceItems as $index => $item) {
			$priceWithMarkup = $item['total_price_with_markup'] / $item['quantity'];
			$customPrice = $item['custom_total'] / $item['quantity'];
			$customTotal = $item['custom_total'] ?? ($customPrice * $item['quantity']);			
			$totalPurchase += $item['total_purchase'];
			$totalPriceWithMarkup += $item['total_price_with_markup'];
			$totalCustom += $customTotal;
			$int = 'шт';
			if($item['types']=='cable'){
				$int = 'м';				
			}elseif($item['types']=='driver'){
				$int = 'км';
			}
			$invoiceView .= '<tr '.($item['types']=='work'?'class="tr_work"':'').'>
				<td >' . ($index + 1) . '</td>
				<td class="description_name mobile_font"><span class="name-onu">' . $item['name'] . '</span></td>
				<td><span class="on_">' . intval($item['quantity']) . ' '.$int.'</span></td>
				<td style="color:#222;">' . number_format($customPrice, 2) . '</td>
				<td style="color:#222;">' . number_format($customTotal, 2) . '</td>
			   </tr>';
		}
		$used_list = '';
		$getlistuser = getListUser();
			$get_list = $db->SimpleWhile("SELECT * FROM shop_used WHERE events_id = '{$invoiceId}'"); 
			if(isset($get_list) && count($get_list)>0){
				$used_list .= '<table class="resp-tab list-onu-olt" style="width: auto;"><thead><tr>';
				$used_list .= '<th>Закріплені працівники</th></tr></thead><tbody><tr><td class="inface_onu">';
				foreach ($get_list as $row) {
					$name = !empty($getlistuser[$row['events_usr']]['name']) 
						? formatPib($getlistuser[$row['events_usr']]['name']) 
						: $getlistuser[$row['events_usr']]['username'];

					$used_list .= '<span class="on_">'.$name.'</span> ';
				}
				$used_list .= '</td></tr></tbody></table>';
			}
		$invoiceView .= '</tbody><tfoot><tr><td colspan="4" style="color:#222;text-align: right;"><strong>Загальна сума:</strong></td><td colspan="2" style="color:#222;"><b>' . number_format($totalCustom, 2) . '</b></td></tr></tfoot></table>
		<div class="flex-right calc_button">
		<img onclick="printPmonCalc()" src="../style/img/calc_printer.png">
		<img onclick="savePmonImage(\'zamovlenia_'.$invoice['id'].'\')" src="../style/img/trending-topic.png">
		<img  onclick="cal_finished(\'user\',\''.$invoice['id'].'\')"  src="../style/img/add-user.png">
		<img class="worker_img" onclick="cal_finished(\'worker\',\''.$invoice['id'].'\')" src="../style/img/decision-making.png">
		</div>
		'.$used_list.'
		<div id="ajax_result"></div>		
		';	
		break;
	case 'used': 
		$events_id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
		if (isset($_POST['usr']) && is_array($_POST['usr'])) {
			$usr = $_POST['usr'];
		} else {
			$usr = [];
		}
		$sanitized_usr = [];
		foreach ($usr as $user_id) {
			if (filter_var($user_id, FILTER_VALIDATE_INT) !== false) {
				$sanitized_usr[] = intval($user_id);
			}
		}
		if (!empty($sanitized_usr) && isset($events_id) && $events_id > 0) {
			$get_list = $db->SimpleWhile("SELECT * FROM shop_used WHERE events_id = '{$events_id}'"); 
			$existing_usr = [];
			foreach ($get_list as $row) {
				$existing_usr[] = $row['events_usr'];
			}
			$to_delete = array_diff($existing_usr, $sanitized_usr);
			if (!empty($to_delete)) {
				$to_delete_ids = implode(',', array_map('intval', $to_delete));
				$db->query("DELETE FROM shop_used WHERE events_id = $events_id AND events_usr IN ($to_delete_ids)");
			}
			$to_add = array_diff($sanitized_usr, $existing_usr);
			foreach ($to_add as $user_id) {
				$db->query("INSERT INTO shop_used (events_id, events_usr) VALUES ($events_id, $user_id)");
			}
		}
		$go->go('/?do=calc&act=view&id='.$events_id);
		exit;
		break;
	case 'list': 
		$metatags = [
			'title' => 'Список накладних',
			'description' => 'Список накладних',
			'page' => 'listcalc'
		];  
		$invoiceView = '
			<div id="onu-speedbar">
				<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
				<a class="brmhref" href="/?do=calc"><i class="fi fi-rr-angle-left"></i>Проектування</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Всі накладні</span>
			</div>
		'; 
		$currentYear = date('Y');
		$monthCounts = [];
		$months = [
			'01' => 'Січень', '02' => 'Лютий', '03' => 'Березень',
			'04' => 'Квітень', '05' => 'Травень', '06' => 'Червень',
			'07' => 'Липень', '08' => 'Серпень', '09' => 'Вересень',
			'10' => 'Жовтень', '11' => 'Листопад', '12' => 'Грудень'
		];		
		foreach ($months as $month => $monthName) {
			$count = $db->Simple("SELECT COUNT(*) as count FROM shop_invoices WHERE YEAR(created_at) = $currentYear AND MONTH(created_at) = $month");
			$monthCounts[$month] = $count['count'];
		}
		$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
		$selectedMonth = intval($selectedMonth); // Ensure it's an integer
		$invoiceView .= '<div class="select_ping3_time">';
		foreach ($months as $month => $monthName) {
			$invoiceView .= '<a href="/?do=calc&act=list&month=' . $month . '" '.($selectedMonth==$month ? 'class="active"':'').'>' . $monthName . ' ' . ($monthCounts[$month]>0 ? '<span class="greens">'.$monthCounts[$month].'</span>' : '') . '</a> ';
		}
		$invoiceView .= '</div>';
		if ($selectedMonth < 1 || $selectedMonth > 12) {
			$selectedMonth = date('m');
		}
		$invoiceItemslist = $db->SimpleWhile("
			SELECT * 
			FROM shop_invoices 
			WHERE YEAR(created_at) = $currentYear 
			  AND MONTH(created_at) = $selectedMonth 
			ORDER BY created_at DESC
		"); 

		// Display invoices
		$invoiceView .= '
		<table class="resp-tab list-onu-olt" style="width: auto;"><thead><tr>
			<th class="mob_w10" width="4%">Статус</th>
			<th width="10%">Додано</th>
			<th>Назва</th>
			<th width="8%">Сума</th>
			<th width="8%">Прихід</th>
			<th width="8%">Закупівля</th>
			<th width="10%">Виконано</th>
			<th width="3%"></th>
			<th width="3%"></th>
			</tr>
			</thead><tbody>';

		foreach ($invoiceItemslist as $index => $item){
			$status = status_shop($item['status']);
			$total = get_total_price($item['id']);
			$invoiceView .= '<tr>
			<td>'.$status.'</td>
			<td class="mobile txt_left"><span class="on_">'.$item['created_at'].'</span></td>
			<td class="description_name mobile_font"><a class="name-onu" href="/?do=calc&act=view&id='.$item['id'].'">'.$item['name'].'</a></td>
			<td class="mobile"><span class="signal2">'.$total['total_price_with_markup'].'</span></td>
			<td class="mobile"><span class="signal3">'.$total['total_price_with_markup'] - $total['total_total_purchase'].'</span></td>
			<td class="mobile"><span class="signal4">'.$total['total_total_purchase'].'</span></td>
			<td class="mobile txt_left">'.(!empty($item['close_at']) && $item['close_at'] !== '0000-00-00 00:00:00' ?'<span class="on_">'.$item['close_at'].'</span>':'').'</td>
			<td><a href="/?do=calc&act=edit&id='.$item['id'].'"><img src="../style/img/edit.png"></a></td>
			<td><a href="/?do=calc&act=delet&id='.$item['id'].'"><img src="../style/img/delet.png"></a></td>
			</tr>';
		}

		$invoiceView .= '</table>';
		break;
	case 'delet': 		
		$invoiceId = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if($invoiceId>0){
			$db->query("DELETE FROM shop_invoice_items WHERE invoice_id = '{$invoiceId}'");
			$db->query("DELETE FROM shop_invoices WHERE id = '{$invoiceId}'");
			$go->go('/?do=calc&act=list');
			exit;
		}
		$go->go('/?do=calc');
		exit;
	break;	
	case 'worker': 	
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$type = isset($_POST['type']) ? intval($_POST['type']) : 0;
		if(isset($id) && $id>0 && isset($type) && $type>0){
			$db->query("UPDATE shop_invoices SET status = '{$type}' WHERE id = '{$id}'");
		}
		$go->go('/?do=calc&act=list');
		exit;
	break;	
	case 'edit':     
    $invoiceId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $invoice = $db->Simple("SELECT * FROM shop_invoices WHERE id = $invoiceId LIMIT 1");
    $invoiceItems = $db->SimpleWhile("SELECT ii.*, p.name, p.types 
                                      FROM shop_invoice_items ii 
                                      JOIN shop_products p ON ii.product_id = p.id 
                                      WHERE ii.invoice_id = $invoiceId");    
    $invoiceView = '
    <form id="editInvoiceForm">
	<table id="formTable" cellspacing="0" cellpadding="0">
		<tr><td class="forminputcalc">
		<div class="body-td">
			<label for="invoice_name">Назва накладної:</label>
			<input type="text" id="invoice_name" name="invoice_name"  value="'.$invoice['name'].'" required>
			<label for="invoice_description">Опис:</label>
			<textarea id="invoice_description" name="invoice_description">'.$invoice['description'].'</textarea>
			<label for="markup">Накрутка (%):</label>
			<input class="pole_warning" type="number" id="markup" name="markup" value="'.$invoice['markup_percentage'].'">
			<br><button id="saveInvoice">Зберегти накладну</button>	
		</div>		
		<td>
		<div class="body-td-s">
			<div class="list-row-div">Загальна сума<div id="pole_custom_total">0 грн</div></div>
			<div class="list-row-div" style="color:#da15d7;">Закупівля<div id="pole_total_purchase">0 грн</div></div>
			<div class="list-row-div" style="color:tomato;">Прихід<div id="pole_total_pruxid">0 грн</div></div>
			<div class="list-row-div" style="color:grey;">Траспортні витрати <div id="pole_driver">0 км</div></div>
			<div class="list-row-div" style="color:green;">Оплата праці<div id="pole_work">0 грн</div></div>
		</td></tr></table>

		<table id="invoiceTable" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th class="col-no">№</th>
					<th class="col-product-name">Назва товару</th>
					<th class="col-type">Тип</th>
					<th class="col-purchase-price">Ціна зак.</th>
					<th class="col-quantity">К-ть</th>
					<th class="col-total-purchase">Сума зак.</th>
					<th class="col-price-with-markup">Ціна</th>
					<th class="col-total-with-markup">Сума</th>
					<th class="col-custom-price">Ціна руч.</th>
					<th class="col-custom-total">Заг</th>
					<th class="col-actions"></th>
				</tr>
			</thead>
			
        <tbody id="invoiceBody">';
        foreach ($invoiceItems as $index => $item){
            $invoiceView .= '<tr>
                <td>'.($index + 1).'</td>
                <td><input type="text" name="product_name[]" value="'.$item['name'].'" required></td>
                <td>
                    <select name="type[]">
                        <option value="device"'.($item['types'] === 'device' ? 'selected' : '').'>Device</option>
                        <option value="cable" '.($item['types'] === 'cable' ? 'selected' : '').'>Cable</option>
                        <option value="work" '.($item['types'] === 'work' ? 'selected' : '').'>Work</option>
                        <option value="driver" '.($item['types'] === 'driver' ? 'selected' : '').'>Driver</option>
                    </select>
                </td>
                <td><input type="number" name="purchase_price[]" value="'.$item['purchase_price'].'" step="0.01" required></td>
                <td><input type="number" name="quantity[]" value="'.$item['quantity'].'" min="1" required></td>
                <td><input type="number" name="total_purchase[]" value="'.$item['total_purchase'].'" readonly></td>
                <td><input type="number" name="price_with_markup[]" value="'.($item['total_price_with_markup'] / $item['quantity']).'" readonly></td>
                <td><input type="number" name="total_with_markup[]" value="'.$item['total_price_with_markup'].'" readonly></td>
                <td><input type="number" name="custom_price[]" value="'.$item['custom_price'].'" step="0.01"></td>
                <td><input type="number" name="custom_total[]" value="'.$item['custom_total'].'" readonly></td>
                <td><button type="button" class="deleteRow">Видалити</button></td>
            </tr>';
        }
    $invoiceView .= '</tbody></table>
	<button id="addRow">Додати рядок</button>
	<button id="Calculator">Порахувати</button>
	<button type="button" id="updateInvoice">Зберегти зміни</button></form>';
    break;
	default:
		$metatags = [
			'title' => 'Проектування',
			'description' => 'Проектування',
			'page' => 'calc'
		];	
		$invoiceView .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Проекти</span>
		</div>
		';
		$invoiceView .= '
			<div class="admin-1">
				<div class="main-panel">
					<div class="admin-zvit">';		
		$invoiceView .= '<a href="/?do=calc&act=list"><img src="../style/img/calculator_list.png"><span>Всі проекти</span></a>';
		$invoiceView .= '<a href="/?do=calc&act=add"><img src="../style/img/calculator_new.png"><span>Нова калькуляція</span></a>';
		$invoiceView .= '
					</div>
				</div>
			</div>';
}
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',$invoiceView.'
<script src="../style/js/shop.js?d=33sdfgsdfbvn3"></script>
<script src="../style/js/html2canvas.min.js"></script>
');
$tpl->compile('content');
$tpl->clear();
?>
