<ul class="raumaushang-list">
    <? foreach ($resources as $resource): ?>
        <li>
            <a href="<?= $controller->url_for('schedules/building/' . $resource->id) ?>">
                <?= htmlReady($resource->parent->name) ?> / <?= htmlReady($resource->name) ?>
            </a>
        </li>
    <? endforeach; ?>
</ul>