<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
$content = '';
$speedbar = '';
$metatags = array(
	'title'=>'Дошка аварій',
	'description'=>'Дошка аварій',
	'page'=>'board_main'
);
$speedbar .='
	<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Дошка аварія</span>
';
$content .= '
<div id="onu-speedbar">
	'.$speedbar.'
</div>
';
$sql_select_board = $pdo->query("SELECT * FROM incident WHERE start_time >= NOW() - INTERVAL 36 HOUR ORDER BY start_time DESC");
$list_board_active = $sql_select_board->fetchAll(PDO::FETCH_ASSOC);
$board_main = '
	<form id="filter-form">
      Початок: <input type="date" name="start_date"><br>
      Кінець: <input type="date" name="end_date"><br>
      Статус:
      <select name="status">
        <option value="">Усі</option>
        <option value="open">В роботі</option>
        <option value="closed">Ліквідовано</option>
      </select>      
	  Aрхів:
      <select name="all">
        <option value="0">Активні</option>
        <option value="1">Архівні</option>
      </select>  
	  На сторінці:
      <select name="limit">
        <option value="10">10</option>
        <option value="20">20</option>
        <option value="30">30</option>
        <option value="40">40</option>
      </select>
	  <label style="display:block;margin: 5px 0;color: #5fa3d0;"><input type="checkbox" id="auto-refresh"> Автооновлення</label>
      <button type="submit">Фільтрувати</button>
    </form>
';
$content .= "<script>
	let autoRefreshInterval;
	let progressInterval;
	let currentRequest = null;
	let progress = 0;
	const refreshTime = 30;
	function loadIncidents(page = 1) {
	  if (currentRequest && currentRequest.readyState !== 4) {
		currentRequest.abort();
	  }
	  currentRequest = $.ajax({
		url: '/?do=board&act=load',
		type: 'POST', 
		data: $('#filter-form').serialize() + '&page=' + page,
		dataType: 'json',
		success: function (response) {
		  $('#board_fault tbody').html(response.tbody);
		  $('#pagination').html(response.pagination);
		},
		complete: function () {
		  currentRequest = null;
		}
	  });
	}
	function startProgressBar() {
		progress = 0;
		$('#progress-container').show();
		$('#progress-bar').css('width', '0%');
		progressInterval = setInterval(() => {
			progress++;
			let percent = (progress / refreshTime) * 100;
			$('#progress-bar').css('width', percent + '%');
			if (progress >= refreshTime) {
				loadIncidents();
				progress = 0;
			}
		}, 1000);
	}
	function stopProgressBar() {
		clearInterval(progressInterval);
		$('#progress-container').hide();
		$('#progress-bar').css('width', '0%');
	}
	$(document).on('submit', '#filter-form', function (e) {
	  e.preventDefault();
	  loadIncidents();
	});
	$(document).on('click', '.page-link', function (e) {
	  e.preventDefault();
	  var page = $(this).data('page');
	  loadIncidents(page);
	});
	$(document).on('change', '#auto-refresh', function() {
		if (this.checked) {
			startProgressBar();
		} else {
			stopProgressBar();
		}
	});
	loadIncidents();
	</script>
	<div class='pmon_block' id='board_fault'>
	<div class='pmon_block_left block_white pre20'>
		" . ( $access->get('board_fault_edit') ? "
		<div class='pole'>		
			<a href='/?do=board&act=add' class='urlelelement'>Нова аварія</a>
			<a href='/?do=board&act=config' class='urlelelement'>Налаштування</a>
		</div>
		" : "")."
		{$board_main}
	</div>
	<div class='pmon_block_right pre80'>
	<div id='progress-container' style='width: 100%; height: 10px; background: #ddd; border-radius: 3px; margin-bottom: 5px; display: none;'>
        <div id='progress-bar' style='height: 100%; width: 0%; background: #4caf50; border-radius: 3px;'></div>
	</div>
    <div class='table-wrapper'>
    <table id='board_fault'>
        <thead>
            <tr>
                <th width='5%'><center>Статус</center></th>
                <th width='15%' class='text_center'>Початок / Кінець</th>
                <th width='25%' >Опис</th>
                <th class='text_center'>Комутатори</th>
                <th class='text_center'>Розташування</th>
            </tr>
        </thead>
        <tbody>
    </tbody></table>
	<div id='pagination'></div>
	</div>
</div>
</div>";
?>
