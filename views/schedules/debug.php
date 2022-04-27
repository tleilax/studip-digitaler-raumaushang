<?php
/**
 * @var SchedulesController $controller
 */
?>
<style>#layout-sidebar { display: none }</style>

<div>
    <a href="#" onclick="$('#iframe')[0].contentWindow.location.reload(true); return false;">
        Reload
    </a>
    <label>
        <input type="radio" name="mode" value="landscape" checked onclick="$('#iframe').attr({width: 1366, height: 768})">
        Landscape
    </label>
    <label>
        <input type="radio" name="mode" value="portrait" onclick="$('#iframe').attr({width: 768, height: 1366})">
        Portrait
    </label>
</div>

<iframe id="iframe" src="<?= $controller->action_link($action, $building->id) ?>" width="1366" height="768"></iframe>
