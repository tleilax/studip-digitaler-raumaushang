<ul class="raumaushang-list">
<? foreach ($resources as $resource): ?>
    <li>
        <a href="<?= $controller->url_for('schedules/building/' . $resource->id) ?>">
            <?= htmlReady($resource->name) ?>
            (<?= htmlReady($resource->category->name) ?>)
        <? if ($resource->description): ?>
            <small><?= htmlReady($resource->description) ?></small>
        <? endif; ?>
        </a>
    </li>
<? endforeach; ?>
<? for ($i = 0; $i < 10; $i += 1): ?>
    <li></li>
<? endfor; ?>
</ul>