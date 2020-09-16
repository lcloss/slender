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
                <option value="{{ databases.value }}"{% if {{ databases.selected }} %} selected="selected"{% endif %}>{{ databases.caption }}</option>
                {% endfor %}
            </select>
        </form>
        {% if "{{ dbname }}" != "" %}
        <h3 class="item-title">Tables</h3>
        {% endif %}
        <div id="tables">
            {% for tables %}
            <div>
                <a class="table-item{% if {{ tables.selected }} %} text-bold{% endif %}" data-toggle="collapse" href="#collapseExample-{{ tables.value }}" aria-expanded="false" aria-controls="collapseExample-{{ tables.value }}">
                    + {{ tables.value }}
                </a>
            </div>
            <div class="collapse" id="collapseExample-{{ tables.value }}">
                <div class="">
                +- <a href="{{ tables.link_structure }}">View structure</a><br />
                +- <a href="{{ tables.link_data }}">Select rows</a>
                </div>
            </div>            
            {% endfor %}
        </div>
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