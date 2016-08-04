<output></output>

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
        <li class="course">
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
                <li><?= htmlReady($teacher->nachname) ?></li>
            <? endforeach; ?>
            </ul>
        </li>
    <? endforeach; ?>
        <li class="empty">
            Aktuell finden keine Veranstaltungen statt
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