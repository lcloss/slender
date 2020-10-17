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
                <option value="{{ databases.value }}"{% if $databases.selected %} selected="selected"{% endif %}>{{ databases.caption }}</option>
                {% endfor %}
            </select>
        </form>
        {% if $dbname != "" %}
        <h3 class="item-title">Tables</h3>
        <div class="tables-container" id="tables">
            {% for tables %}
            <div>
                <a class="table-item{% if $tables.selected %}  text-bold{% endif %}" data-toggle="collapse" href="#collapseExample-{{ tables.value }}" {% if $tables.selected %}aria-expanded="true"{% else %}aria-expanded="false"{% endif %} aria-controls="collapseExample-{{ tables.value }}"><i class="fa fa-database" aria-hidden="true"></i> {{ tables.value }}</a>
            </div>
            <div class="collapse list-group table-operations{% if $tables.selected %} show{% endif %}" id="collapseExample-{{ tables.value }}">
                <a class="table-item-o" href="{{ tables.link_structure }}"><i class="fa fa-cogs" aria-hidden="true"></i> View structure</a>
                <a class="table-item-o" href="{{ tables.link_data }}"><i class="fa fa-table" aria-hidden="true"></i> Select rows</a>
            </div>            
            {% endfor %}
        </div>
        {% endif %}
    </div>
    <div class="col-8">
    {% if $op == "struct" %}
    <h3>Table: <strong>{{ tbname }}</strong></h3>
    <h4>Structure</h4>
    <div class="overflow-auto">
        <table class="table table-striped">
            <thead class="thead-dark">
            <tr>
                <th scope="col">Id</th>
                <th scope="col">Field</th>
                <th scope="col">Type</th>
                <th scope="col">Null</th>
                <th scope="col">Key</th>
                <th scope="col">Default</th>
            </tr>
            </thead>
            <tbody>
            {% for columns %}
            <tr>
                <td scope="row">{{ columns.id }}</td>
                <td>{{ columns.name }}</td>
                <td>{{ columns.type }}</td>
                <td>{{ columns.null }}</td>
                <td>{{ columns.key }}</td>
                <td>{{ columns.default }}</td>
            </tr>
            {% endfor %}
            </tbody>
        </table>
    </div>
    {% endif %}

    {% if $op == "list" %}
    <h3>Table: <strong>{{ tbname }}</strong></h3>
    <h4>Data</h4>
    <h5>{{ tbindex }}</h5>
    <div class="overflow-auto">
        <table class="table table-striped">
            <thead class="thead-dark">
            <tr>
                {% for tbheader %}
                <th scope="col">{{ tbheader.column }}</th>
                {% endfor %}
            </tr>
            </thead>
            <tbody>
            {% for tbdata %}
            <tr>
                <td scope="row">{{ tbdata.id }}</td>
                {% for tbdata.columns %}
                <td>{{ tbdata.columns.column }}</td>
                {% endfor %}
            </tr>
            {% endfor %}
            </tbody>
        </table>
    </div>
    {% endif %}
    </div>
</div>

{% block scripts %}
<script>
$( '#dbname' ).change(function(e) {
    // alert( this.value );
    if ( this.value == '' ) {
        window.location = 'db.php';
    } else {
        window.location = 'db.php?dbname=' + this.value;
    }
    
});
function sayHello(msg) {
    alert(msg);
}
</script>
{% endblock %}