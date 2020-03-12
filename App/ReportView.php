<form method="post">
    <input name="start" value="<?= $start->format('Y-m-d') ?>">
    <input name="finish" value="<?= $finish->format('Y-m-d') ?>">
    <input type="submit">
</form>
<?
$arCols = [
    'path'          => 'Иерархия подчинения',
    'low_work_days' => 'дней недоработки',
    'Email'         => 'Email',
    'worked_sec'    => 'Отработано',
    'd'             => 'Отработано с учетом подчиненных',
];
echo '<table border="1" class="table">';
echo '<tr>';
foreach (array_keys($result[array_key_first($result)]) as $key) {
    if (!array_key_exists($key, $arCols)) continue;
    echo '<th>';
    echo $arCols[$key];
    echo '</th>';
}
echo '</tr>';

foreach ($result as $row) {

    $data = ' ' . $row['Email']['val'] . '<br>' . $row['Info'] . '';// json_encode(,JSON_UNESCAPED_UNICODE)
    echo '<tr data-title="<b>' . $row['path'] . '</b>" data-text="' . base64_encode($data) . '">';
    foreach ($row as $k => $cell) {
        if (!array_key_exists($k, $arCols)) continue;
        if ($k == 'low_work_days') {
            $dd = '';
            $r = explode(',', $row['DD']);
            $r = array_diff($r, ['']);
            foreach ($r as $day) {
                $arDay = explode('=', $day);

                $dd .= $arDay[0] . ': ' . reportModel::seconds_to_time($arDay[1]) . '<br>';
            }
//            $dd = $row['DD'];
            echo '<td data-text="' . base64_encode('Недоработки<br>' . $dd) . '">';
        } else {
            echo '<td>';
        }

        if (is_array($cell)) {
            if (
            !$cell['valid']
            ) {
                echo "<span style='color:red'>" . $cell['val'] . "</span>";
            } else {
                echo $cell['val'];
            }
        } else {
            echo $cell;
        }
        echo '</td>';
    }
    echo '</tr>';
}
echo '</table>';
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


<script>
    $(document).tooltip({
        track: true,
        items: "[title],[data-text]",
        content: function () {
            var element = $(this);
            var text = '';
            if (element.is("[data-title]")) {
                text = element.data("title");
            } else if (element.is("[data-text]")) {
                text = element.parent().data("title");
            }
            if (element.is("[data-text]")) {
                text = text + '<br>' + '' + b64_to_utf8(element.data("text"));
            }
            return text;
        },
    });

    $(document).tooltip();

    function b64_to_utf8(str) {
        str = str.replace(/\s/g, '');
        return decodeURIComponent(escape(window.atob(str)));
    }
</script>


