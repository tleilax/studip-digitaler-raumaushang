<?php
    $monday = strtotime('this monday');
    $week_days = [];
    for ($i = 0; $i < 5; $i += 1) {
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
    <? if (Studip\ENV === 'development'): ?>
        <small>(<?= date('d.m.Y H:i:s') ?>)</small>
    <? endif; ?>
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
            <th><?= _('Uhrzeit') ?></th>
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

<? return; ?>

<ul class="raumaushang-schedule">
<? foreach ($schedule as $date): ?>
    <li <? if ($date->is_current) echo 'class="is-current"'; ?>>
    <? if ($show_rooms): ?>
        <div class="schedule-room">
            <?= htmlReady($date->resource->name) ?>
        </div>
    <? endif; ?>
        <div class="schedule-dates">
            <?= strftime('%x %X', $date->begin) ?> - <?= strftime('%x %X', $date->end) ?><br>
        </div>
        <div class="schedule-title">
            <span class="schedule-course-title"><?= htmlReady($date->code) ?></span>
            <?= htmlReady($date->name) ?>
        </div>
    <? if ($date->teachers): ?>
        <div class="schedule-teachers">
            (<?= htmlReady($date->teachers) ?>)
        </div>
    <? endif; ?>
    </li>
<? endforeach; ?>
</ul>
