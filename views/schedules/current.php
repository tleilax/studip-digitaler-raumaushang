<?php
/**
 * @var Raumaushang\Resources\Objekt $building
 * @var Raumaushang\Schedule[] $dates
 * @var int $total
 * @var int $max
 * @var int $page
 */
?>
<output></output>

<header>
    <h1>Veranstaltungen</h1>
    <h2 class="landscape"><?= sprintf('Gebäude %s', htmlReady($building->name)) ?></h2>
    <h2 class="portrait"><?= sprintf('Geb. %s', htmlReady($building->name)) ?></h2>

    <aside class="date-and-clock">
        <time><?= date('H:i') ?></time>
        <date><?= strftime('%A, %x') ?></date>
    </aside>
</header>

<section>
    <ul class="courses">
    <? foreach ($dates as $date): ?>
        <li class="course">
            <div class="time">
                <?= date('H:i', $date->begin) ?>
                -
                <?= date('H:i', $date->end) ?>
            </div>
            <div class="room">
                <?= htmlReady($date->room) ?>
            </div>
            <div class="title">
                <?= htmlReady($date->code . ' ' . $date->name) ?: 'Keine Angaben' ?>
            </div>
            <ul class="teachers">
            <? foreach ($date->teachers as $teacher): ?>
                <li><?= htmlReady($teacher->nachname) ?></li>
            <? endforeach; ?>
            </ul>
        </li>
    <? endforeach; ?>
        <li class="empty">
            <p>
                Herzlich Willkommen!<br>
                <br>
                Aktuell finden keine Veranstaltungen statt
            </p>
        </li>
    </ul>
</section>

<footer <? if ($total <= $max) echo 'style="display: none;"'; ?>>
    <div class="progress"></div>

    Seite
    <span class="current-page"><?= $page + 1 ?></span>
    /
    <span class="total-pages"><?= ceil($total / $max) ?></span>
    -
    Bitte warten, weitere Veranstaltungen auf Seite
    <span class="next-page"><?= $page + 2 ?></span>
    &raquo;

</footer>
