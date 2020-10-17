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
<label for="url-input">URL copied from Google search results</label>
<div class="input-group">
    <input type="text" class="form-control" id="url-input" autofocus onFocus="this.select();" placeholder="Paste a URL copied from Google search results">
    <span class="input-group-btn">
    <button class="btn btn-primary" type="button" onclick="updateUrl()">Clean it!</button>
    </span>
</div><!-- /input-group -->

<div class="well" id="hidden-well" style="margin-top: 15px; visibility: hidden;">
    <span id="message-span"></span><a id="clean-link"></a>
</div>
<p id="cleaned-further-message" style="display: none;">
    This can possibly be cleaned further, leaving the following URL.
</p>
<div class="well" id="hidden-well2" style="margin-top: 15px; display: none;">
    <a id="clean-link2"></a>
</div>
{% block scripts %}
<script>
couldNotExtract = function() {
    document.getElementById("clean-link").setAttribute("href", "");
    document.getElementById("clean-link").textContent="";
    document.getElementById("message-span").textContent="Sorry, URL Clean couldn't extract the link.";
    document.getElementById("hidden-well").style.visibility="visible";
}

updateUrl = function() {
    document.getElementById("hidden-well2").style.display="none";
    document.getElementById("cleaned-further-message").style.display="none";

    var str = document.getElementById("url-input").value;

    var isSearchResultUrl = false;

    if (new RegExp('^http[^?]*\/search\\?').test(str)) {
        var parts = str.split("?");
        if (parts.length != 2) {
            couldNotExtract();
            return;
        }
        var part0 = parts[0];
        var part1 = "?" + parts[1];
        var tbm = "";  // e.g. for image search
        if (new RegExp('&tbm=').test(str)) {
            tbm = part1.replace(/.*?[&?]tbm=/i, "");
            tbm = "&tbm=" + tbm.replace(/&.*/, "");
        }
        part1 = part1.replace(/.*?[&?]q=/i, "");
        part1 = part1.replace(/&.*/, "");
        str = part0 + "?q=" + part1 + tbm;
    } else if (new RegExp('^http.*[&?]url=').test(str)) {
        str = str.replace(/.*?[&?]url=/i, "");
        str = str.replace(/&.*/, "");
        str = decodeURIComponent(str);
        isSearchResultUrl = true;
    } else {
        var parts = str.split("?");
        if (parts.length != 2) {
            couldNotExtract();
            return;
        }
        str = parts[0];
    }

    var httpFtpPattern=new RegExp("^(http:\/\/|https:\/\/|ftp:\/\/).*$");
    var disallowedCharPattern = new RegExp('[<>"]');
    if (httpFtpPattern.test(str) && !disallowedCharPattern.test(str)) {
        document.getElementById("clean-link").setAttribute("href", str);
        document.getElementById("clean-link").textContent=str;
        document.getElementById("message-span").textContent="";
        document.getElementById("hidden-well").style.visibility="visible"
        if (isSearchResultUrl) {
            var parts = str.split("?");
            if (parts.length == 2) {
                document.getElementById("hidden-well2").style.display="block";
                document.getElementById("cleaned-further-message").style.display="block";
                document.getElementById("clean-link2").setAttribute("href", parts[0]);
                document.getElementById("clean-link2").textContent=parts[0];
            }
        }
    } else {
        couldNotExtract();
    }
}
</script>
{% endblock %}
