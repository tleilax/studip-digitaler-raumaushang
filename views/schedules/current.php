<?php
$directions = ['', 'direction-left', 'direction-up-left', 'direction-right', 'direction-up-right']; ?>

<header>
<? //    <h1>Veranstaltungswegweiser</h1> ?>
    <h1>Veranstaltungen</h1>
    <h2><?= sprintf('Gebäude %s', htmlReady($building->name)) ?></h2>

    <aside class="date-and-clock">
        <time><?= date('H:i') ?></time>
        <date><?= strftime('%A, %x') ?></date>
    </aside>
</header>

<section>
    <ul class="courses">
    <? foreach ($dates as $date): ?>
        <li class="<?= $directions[array_rand($directions)] ?>">
            <span class="time">
                <?= date('H:i', $date->begin) ?>
                -
                <?= date('H:i', $date->end) ?>
            </span>
            <span class="room">
                <?= htmlReady($date->room) ?>
            </span>
            <span class="title">
                <?= htmlReady($date->name) ?>
            </span>
            <ul class="teachers">
            <? foreach ($date->teachers as $teacher): ?>
                <li>
                    <?= htmlReady($teacher->nachname) ?>
                </li>
            <? endforeach; ?>
            </ul>
        </li>
    <? endforeach; ?>
    </ul>
</section>

<footer>
<? if ($total > $max): ?>
    <?= sprintf('Seite %u/%u', $page + 1, ceil($total / $max)) ?>
    -
    <?= sprintf('Bitte warten, weitere Veranstaltungen auf Seite %u', $page + 2) ?> &raquo;
<? endif; ?>
</footer>