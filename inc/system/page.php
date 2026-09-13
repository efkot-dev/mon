<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

$speedbar = '';
$result = '';
switch($act) {
	case 'listgroup':
		echo "<h2>Категорії</h2>";
		$res = $mysqli->query("SELECT * FROM gr_page ORDER BY id DESC");
		while ($row = $res->fetch_assoc()) {
			echo "<p><b>{$row['name']}</b> — <a href='?act=newpage&id={$row['id']}'>Додати сторінку</a> | <a href='?act=viewgroup&id={$row['id']}'>Переглянути сторінки</a></p>";
		}
		echo "<p><a href='?act=addgroup'>+ Нова категорія</a></p>";
		break;

	case 'addgroup':
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$name = $_POST['name'];
			$template = $_POST['template'];
			$stmt = $mysqli->prepare("INSERT INTO gr_page (name, template, added) VALUES (?, ?, NOW())");
			$stmt->bind_param("ss", $name, $template);
			$stmt->execute();
			echo "<p>Категорія створена!</p><a href='?act=listgroup'>Повернутись</a>";
			break;
		}
		?>
		<h2>Створення категорії + шаблон</h2>
		<form method="post">
			Назва категорії:<br>
			<input type="text" name="name" required><br><br>

			<div id="fields"></div>
			<button type="button" onclick="addField()">+ Додати поле</button><br><br>

			Шаблон (генерується автоматично):<br>
			<textarea name="template" id="output" rows="10" cols="60" readonly></textarea><br><br>

			<input type="submit" value="Створити категорію">
		</form>

		<script>
		let fieldsArray = [];

		function transliterate(str) {
			return str.toLowerCase().replace(/[а-яґєії]/g, function (char) {
				const map = {
					'а':'a','б':'b','в':'v','г':'h','ґ':'g','д':'d','е':'e','є':'ie','ж':'zh','з':'z','и':'y',
					'і':'i','ї':'i','й':'i','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r','с':'s',
					'т':'t','у':'u','ф':'f','х':'kh','ц':'ts','ч':'ch','ш':'sh','щ':'shch','ь':'','ю':'iu','я':'ia'
				};
				return map[char] || char;
			}).replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
		}

		function addField() {
			const container = document.getElementById('fields');
			const div = document.createElement('div');

			const input = document.createElement('input');
			input.type = 'text';
			input.placeholder = 'Назва поля';
			input.onchange = function () {
				const label = input.value.trim();
				const field = transliterate(label);
				if (label && !fieldsArray.find(f => f.label === label)) {
					fieldsArray.push({ label, field });
					updateJSON();
					input.disabled = true;
				}
			};

			const removeBtn = document.createElement('button');
			removeBtn.textContent = '🗑';
			removeBtn.onclick = function () {
				const label = input.value.trim();
				fieldsArray = fieldsArray.filter(f => f.label !== label);
				div.remove();
				updateJSON();
			};

			div.appendChild(input);
			div.appendChild(removeBtn);
			container.appendChild(div);
		}

		function updateJSON() {
			document.getElementById('output').value = JSON.stringify(fieldsArray, null, 2);
		}
		</script>
		<?php
		break;

	case 'newpage':
		$group_id = (int)$_GET['id'];
		$res = $mysqli->query("SELECT * FROM gr_page WHERE id = $group_id");
		$group = $res->fetch_assoc();
		$template = json_decode($group['template'], true);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$data = [];
			foreach ($template as $field) {
				$data[$field['field']] = $_POST[$field['field']] ?? '';
			}
			$json = json_encode($data);
			$stmt = $mysqli->prepare("INSERT INTO gr_page_list (group_id, data, added) VALUES (?, ?, NOW())");
			$stmt->bind_param("is", $group_id, $json);
			$stmt->execute();
			echo "<p>Сторінка додана!</p><a href='?act=viewgroup&id=$group_id'>Назад</a>";
			break;
		}

		echo "<h2>Нова сторінка для: {$group['name']}</h2>";
		echo "<form method='post'>";
		foreach ($template as $field) {
			echo "{$field['label']}<br>";
			echo "<input type='text' name='{$field['field']}'><br><br>";
		}
		echo "<input type='submit' value='Зберегти'>";
		echo "</form>";
		break;

	case 'viewgroup':
		$group_id = (int)$_GET['id'];
		$res = $mysqli->query("SELECT * FROM gr_page WHERE id = $group_id");
		$group = $res->fetch_assoc();
		$template = json_decode($group['template'], true);
		echo "<h2>Сторінки для категорії: {$group['name']}</h2>";

		$res = $mysqli->query("SELECT * FROM gr_page_list WHERE group_id = $group_id ORDER BY id DESC");
		while ($row = $res->fetch_assoc()) {
			$data = json_decode($row['data'], true);
			echo "<div style='border:1px solid #ccc; padding:10px; margin:5px'>";
			foreach ($template as $field) {
				$val = htmlspecialchars($data[$field['field']] ?? '');
				echo "<b>{$field['label']}:</b> $val<br>";
			}
			echo "</div>";
		}
		echo "<p><a href='?act=newpage&id=$group_id'>+ Додати сторінку</a></p>";
		break;

	default:
		echo "<a href='?act=listgroup'>До списку категорій</a>";
		break;
}
$tpl->load_template('battery/page.tpl');
$tpl->set('{speedbar}', $speedbar);
$tpl->set('{result}', $result);
$tpl->compile('content');
$tpl->clear();
?>
