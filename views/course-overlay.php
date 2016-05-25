<h2>{{code}} {{name}}</h2>
<div class="dates">
    <date class="begin">{{begin}}</date>
    <span> bis </span>
    <date class="end">{{end}}</date>
</div>

<svg class="qrcode" data-course-id="{{course_id}}"><g/></svg>

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
    <dt>Module</dt>
{{#modules}}
    <dd>{{.}}</dd>
{{/modules}}
</dl>
{{/hasModules}}

{{#description}}
<small style="white-space: pre-line;">{{description}}</small>
{{/description}}
