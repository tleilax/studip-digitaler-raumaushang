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
            <?= Icon::create('phone', 'info')->asImg(36, ['style' => 'vertical-align: text-top']) ?>
            798 <strong>4468</strong>
        </li>
        <li>
            <?= Icon::create('mail', 'info')->asImg(36, ['style' => 'vertical-align: text-top']) ?>
            thomas.hots@uni-oldenburg.de
        </li>
    </ul>
<? endif; ?>
    <hr>
    <p>
        Bei Raumleerständen oder bei einem Raumtausch bitte eine Meldung an das
    </p>
    <ul class="contact">
        <li>Raumbüro</li>
        <li>
            <?= Icon::create('phone', 'info')->asImg(36, ['style' => 'vertical-align: text-top']) ?>
            798 <strong>2483</strong> / <strong>2545</strong> / <strong>4273</strong>
        </li>
        <li>
            <?= Icon::create('mail', 'info')->asImg(36, ['style' => 'vertical-align: text-top']) ?>
            raumbuero@uni-oldenburg.de
        </li>
    </ul>
</div>
