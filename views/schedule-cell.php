<td class="{{class}}" data-course-id="{{id}}" data-day="{{day}}" data-slot="{{slots}}" rowspan="{{duration}}">
    <div class="name">{{code}} {{name}}</div>
{{#hasTeachers}}
    <ul class="teachers">
    {{#teachers}}
        <li>{{nachname}}</li>
    {{/teachers}}
    </ul>
{{/hasTeachers}}
</td>