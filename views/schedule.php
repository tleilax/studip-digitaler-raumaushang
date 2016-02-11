<ul class="raumaushang-schedule">
<? foreach ($schedule as $date): ?>
    <li <? if ($date->is_current) echo 'class="is-current"'; ?>>
    <? if ($show_rooms): ?>
        <div class="schedule-room">
            <?= htmlReady($date->resource->name) ?>
        </div>
    <? endif; ?>
        <div class="schedule-dates">
            <?= strftime('%X', $date->begin) ?> - <?= strftime('%X', $date->end) ?><br>
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
