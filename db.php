<?php ?>
<?php
include 'config/bootstrap.php';

$nl = "<br />\n";
$server = "localhost";
$server_ip = "127.0.0.1";
$dbuser = "root";
$dbpass = "";
$dbname = "mysql";
$mysqli = new mysqli($server, $dbuser, $dbpass, $dbname);

$content = "";

if ($mysqli->connect_errno) {
    $content .= "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$content .= $mysqli->host_info . $nl;

$mysqli = new mysqli($server_ip, $dbuser, $dbpass, $dbname, 3306);
if ($mysqli->connect_errno) {
    $content .= "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$content .= $mysqli->host_info . $nl;

$databases = "";
$databases .= "<h2>Databases</h2>";
$db_list = mysqli_query($mysqli, "SHOW DATABASES");
$databases .= "<ul>";
foreach($db_list as $db_name) {
    if (isset($_GET['dbname'])) {
        if ($_GET['dbname'] == $db_name['Database']) {
            $style = "style='font-weight: bold;'";
        } else {
            $style = "";
        }
    } else {
        $style = "";
    }
    $databases .= "<li $style><a href='db.php?dbname=" . $db_name['Database'] . "'>" . $db_name['Database'] . "</a></li>";
    // var_dump($db_name);
}
$databases .= "<ul>";

if (isset($_GET['dbname'])) {
    $mysqli->select_db($_GET['dbname']);
    $tables = "<h2>Database: " . $_GET['dbname'] . "</h2>";
    $tables .= "<h3>Tables</h3>";

    // $tb_list = mysqli_query($mysqli, "SHOW TABLES FROM " . $_GET['dbname']);
    $tb_list = mysqli_query($mysqli, "SHOW TABLES");
    $tables .= "<ul>";
    $key = "Tables_in_" . $_GET['dbname'];
    foreach($tb_list as $tb_name) {
            if (isset($_GET['tbname'])) {
                if ($_GET['tbname'] == $tb_name[$key]) {
                    $style = "style='font-weight: bold;'";
                } else {
                    $style = "";
                }
            } else {
                $style = "";
            }
            $tables .= "<li $style><a href='db.php?dbname=" . $_GET['dbname'] . "&tbname=" . $tb_name[$key] . "'>" . $tb_name[$key] . "</a></li>";
        // var_dump($tb_name);
    }
    $tables .= "<ul>";
} else {
    $tables = "";
}

if (isset($_GET['tbname'])) {
    $columns = "<h3>Table: " . $_GET['tbname'] . "</h3>";
    $columns .= "<h4>Columns</h4>";
    $col_list = mysqli_query($mysqli, "SHOW COLUMNS FROM " . $_GET['tbname']);
    $columns .= "<table cellpadding='4'>";
    $columns .= "<tr><th width='380px'>Field</th><th width='180px'>Type</th><th width='80px'>Null</th><th width='80px'>Key</th><th width='80px'>Default</th><th width='80px'>Extra</th></tr>";
    $key = "Tables_in_" . $_GET['dbname'];
    foreach($col_list as $col_name) {
        $columns .= "<tr><td>" . $col_name['Field'] . "</td><td>" . $col_name['Type'] . "</td><td>" . $col_name['Null'] . "</td><td>" . $col_name['Key'] . "</td><td>" . $col_name['Default'] . "</td><td>" . $col_name['Extra'] . "</td></tr>";
        // var_dump($col_name);
    }
    $columns .= "<table>";
} else {
    $columns = "";
}

$content .= "<div class='row'>";
$content .= "<div class='col-2'>$databases</div>";
$content .= "<div class='col-3'>$tables</div>";
$content .= "<div class='col-7'>$columns</div>";
$content .= "</div>";
$template = new Template('index');
$data = array(
    'title' => 'DB',
    'content' => $content
);
$template->display($data);
