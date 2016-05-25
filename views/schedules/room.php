<?php
    $monday = strtotime('this monday', $begin);
    $week_days = [];
    for ($i = 0; $i < max($config['display_days']); $i += 1) {
        $week_days[] = strftime('%A, %x', strtotime('+' . $i . ' days', $monday));
    }

    $print_day = function ($offset) {
        return strftime('%A', strtotime('+' . ($offset - 1) . ' days', strtotime('this monday')));
    };
?>

<header>
    <h1>
        <a href="<?= $_SERVER['REQUEST_URI'] ?>">
            <?= sprintf(_('Raum %s'), htmlReady($room->name)) ?>
        </a>
        <small></small>
    </h1>
</header>
<table class="week-schedule" data-resource-id="<?= $id ?>">
    <colgroup>
        <col width="5%">
    <? foreach ($config['display_days'] as $day): ?>
        <col width="<?= round(95 / count($config['display_days']), 2) ?>%">
    <? endforeach; ?>
    </colgroup>
    <thead>
        <tr>
            <th>&nbsp;</th>
        <? foreach ($config['display_days'] as $day): ?>
            <th data-day="<?= $day ?>">
                <?= htmlReady($print_day($day)) ?>
                <date></date>
            </th>
        <? endforeach; ?>
        </tr>
    </thead>
    <tbody>
    <? foreach (range(8, 20, 2) as $slot): ?>
        <tr data-slot="<?= $slot ?>">
            <th><?= $slot ?>:00</th>
        <? foreach ($config['display_days'] as $day): ?>
            <td data-day="<?= $day ?>">&nbsp;</td>
        <? endforeach; ?>
        </tr>
        <tr data-slot="<?= $slot + 1 ?>">
            <th><?= $slot + 1 ?>:00</th>
        <? foreach ($config['display_days'] as $day): ?>
            <td data-day="<?= $day ?>">&nbsp;</td>
        <? endforeach; ?>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>

<script id="course-template" type="x-tmpl-mustache"><?= $this->render_partial('course-overlay.php') ?></script>
<script id="schedule-cell-template" type="x-tmpl-mustache"><?= $this->render_partial('schedule-cell.php') ?></script>
