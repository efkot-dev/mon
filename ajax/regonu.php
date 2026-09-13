<?php
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';

$templatereg = ROOT_DIR . '/file/template/';
$name = isset($_POST['name']) ? Clean::text($_POST['name']) : null;
$act = isset($_POST['act']) ? Clean::text($_POST['act']) : null;
if ($act == 'add') {
    $filePath = $templatereg . 'data.service';
    if (file_exists($filePath)) {
        $currentData = file_get_contents($filePath);
        $dataByKeys = [];
        $keyValuePairs = explode(';', trim($currentData, ";\n"));
        foreach ($keyValuePairs as $keyValuePair) {
            list($key, $values) = explode(':', $keyValuePair);
            $dataByKeys[trim($key)] = explode(',', trim($values));
        }
    } else {
        echo 'empty';
        exit();
    }
?>
<form id="serviceForm" method="post" action="javascript:void(0)" class="flex">

    <input type="hidden" name="act" id="act" value="saveservice">
    <input type="hidden" name="temp" value="<?= $name ?>">
    <input type="hidden" name="type" value="add">
    <select name="name" id="name" class="css_select">
        <option value="empty"></option>
        <?php foreach ($dataByKeys as $key => $values) : ?>
            <option value="<?= $key ?>"><?= $key ?></option>
        <?php endforeach; ?>
    </select>
    <div id="valueView"></div>
    <button class="css_add" onclick="sendunregistr('<?= $name ?>', 'add')"><?= $lang['add'] ?></button>
</form>
<script>
    const valueSelectContainer = document.getElementById('valueView');
    const keySelect = document.getElementById('name');

    keySelect.addEventListener('change', function () {
        const selectedKey = this.value;
        const values = <?= json_encode($dataByKeys) ?>;

        if (values[selectedKey]) {
            const valueSelect = document.createElement('select');
            valueSelect.name = 'value';
            valueSelect.id = 'value';
            valueSelect.className = 'css_select';

            values[selectedKey].forEach(value => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = value;
                valueSelect.appendChild(option);
            });

            while (valueSelectContainer.firstChild) {
                valueSelectContainer.removeChild(valueSelectContainer.firstChild);
            }

            valueSelectContainer.appendChild(valueSelect);
        } else {
            while (valueSelectContainer.firstChild) {
                valueSelectContainer.removeChild(valueSelectContainer.firstChild);
            }
        }
    });
</script>
<?php
}elseif($act=='olt'){
	$dataByKeys = $db->Multi('switch');
?>
<form id="serviceForm" method="post" action="javascript:void(0)" class="flex">

    <input type="hidden" name="act" id="act" value="saveaccesolt">
    <input type="hidden" name="temp" value="<?= $name ?>">
    <input type="hidden" name="type" value="add">
    <input type="hidden" name="name" id="name" value="dozvoleno">
    <select name="value" id="value" class="css_select">
        <option value="empty"></option>
        <?php foreach ($dataByKeys as $key => $values) : ?>
            <option value="<?= $values['id'] ?>"><?= $values['place'] ?></option>
        <?php endforeach; ?>
    </select>
    <div id="valueView"></div>
    <button class="css_add" onclick="sendunregistr('<?= $name ?>', 'saveolt')"><?= $lang['add'] ?></button>
</form><?php	
}
?>
