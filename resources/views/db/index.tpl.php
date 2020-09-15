<?php
if (!defined('INSIDE')) {
    die("Silence is golden.");
} else {
    if (INSIDE != true) {
        die("Silence is golden.");
    }
}
?>
{% use index %}
<div class="row">
    <div class="col-4">
        <h2 class="item-title">Databases</h2>        
        <form>
            <select class="form-control" name="dbname" id="dbname">
                <option value="">(Selecione)</option>
                {% for databases %}
                <option value="{{ databases.value }}" {% if tables.selected %}selected="selected"{% endif %}>{{ databases.caption }}</option>
                {% endfor %}
            </select>
        </form>
        <h3 class="item-title">Tables</h3>
        <div id="tables">
        {% for tables %}
        <div id="table-name-{{ tables.value }}">
            <div class="table-item" data-toggle="collapse" data-target="#table-options-{{ tables.value }}" aria-expanded="false" aria-controls="table-options-{{ tables.value }}">+ {{ tables.value }}</div>
        </div>
        <div class="collapase" id="table-options-{{ tables.value }}" aria-labelledby="table-name-{{ tables.value }}" data-parent="#tables">
        +- <a href="{{ tables.link_structure }}">View structure</a><br />
        +- <a href="{{ tables.link_data }}">Select rows</a>
        </div>
        {% endfor %}
        </div>
        </ul>
    </div>
    <div class="col-8"></div>
</div>

{% block scripts %}
<script>
$( '#dbname' ).change(function(e) {
    // alert( this.value );
    window.location = 'db.php?dbname=' + this.value;
});
function sayHello(msg) {
    alert(msg);
}
</script>
{% endblock %}