<div>
    <h2><?= sprintf(_('Rauminformationen %s'), htmlReady($room->name)) ?></h2>
    <hr>
    <dl>
    <? foreach ($properties as $key => $value): ?>
        <dt><?= htmlReady($key) ?></dt>
        <dd><?= htmlReady($value) ?></dd>
    <? endforeach; ?>
    </dl>

<? if (!empty($properties['Beamer'])): ?>
    <hr>
    <p>
        Bei Problemen mit dem Beamer wenden Sie sich bitte an:
    </p>
    <ul class="contact">
        <li>Thomas Hots</li>
        <li>
            <?= Icon::create('36/black/phone', ['style' => 'vertical-align: text-top'])->render(Icon::SVG) ?>
            798 <strong>4468</strong>
        </li>
        <li>
            <?= Icon::create('36/black/mail', ['style' => 'vertical-align: text-top'])->render(Icon::SVG) ?>
            thomas.hots@uni-oldenburg.de
        </li>
    </ul>
<? endif; ?>
</div>