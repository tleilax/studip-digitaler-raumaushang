<section class="course-overlay">

    <header>
        <h2>{{code}} {{name}}</h2>
    </header>
    <article>
    <? if (Config::get()->RAUMAUSHANG_SHOW_QRCODES): ?>
        <div class="qrcode" data-course-id="{{course_id}}"></div>
    <? endif; ?>

        <div class="dates">
            <date class="begin">{{begin}}</date>
            <span> bis </span>
            <date class="end">{{end}}</date>
        </div>

    {{#hasTeachers}}
        <dl class="teachers">
            <dt>Lehrende</dt>
        {{#teachers}}
            <dd>{{name_full}}</dd>
        {{/teachers}}
        </dl>
    {{/hasTeachers}}

    {{#hasModules}}
        <dl>
            <dt>Studienmodule</dt>
        {{#modules}}
            <dd>{{.}}</dd>
        {{/modules}}
        </dl>
    {{/hasModules}}

    {{#description}}
        <div class="description">{{description}}</div>
    {{/description}}
    </article>

</section>
