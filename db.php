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
$link = "db.php";

$databases = "";
$databases .= "<h2>Databases</h2>";
$db_list = mysqli_query($mysqli, "SHOW DATABASES");
$databases .= "<ul>";
$arr_databases = [];
foreach($db_list as $db_name) {
    $arr_databases[] = array(
        'value' => $db_name['Database'],
        'caption' => $db_name['Database']
    );
    if (isset($_GET['dbname'])) {
        if ($_GET['dbname'] == $db_name['Database']) {
            $style = "style='font-weight: bold;'";
        } else {
            $style = "";
        }
    } else {
        $style = "";
    }
    $databases .= "<li $style><a href='" . $link . "?dbname=" . $db_name['Database'] . "'>" . $db_name['Database'] . "</a></li>";
    // var_dump($db_name);
}
$databases .= "<ul>";
$arr_tables = [];
if (isset($_GET['dbname'])) {
    $dblink = $link . "?dbname=" . $_GET['dbname'];
    $mysqli->select_db($_GET['dbname']);
    $tables = "<h2>Database: " . $_GET['dbname'] . "</h2>";
    $tables .= "<h3>Tables</h3>";

    // $tb_list = mysqli_query($mysqli, "SHOW TABLES FROM " . $_GET['dbname']);
    $tb_list = mysqli_query($mysqli, "SHOW TABLES");
    $tables .= "<ul>";
    $key = "Tables_in_" . $_GET['dbname'];
    foreach($tb_list as $tb_name) {
        $arr_tables[] = array(
            'value' => $tb_name[$key],
            'caption' => $tb_name[$key],
            'link_structure' => $dblink . "&tbname=" . $tb_name[$key],
            'link_data' => $dblink . "&tbname=" . $tb_name[$key] . "&list=true",
            'selected' => ($tb_name[$key] == $_GET['dbname'] ? true : false)
        );
        if (isset($_GET['tbname'])) {
            if ($_GET['tbname'] == $tb_name[$key]) {
                $style = "style='font-weight: bold;'";
            } else {
                $style = "";
            }
        } else {
            $style = "";
        }
        $tables .= "<li $style><a href='" . $dblink . "&tbname=" . $tb_name[$key] . "'>" . $tb_name[$key] . "</a></li>";
    }
    $tables .= "<ul>";
} else {
    $dblink = $link;
    $tables = "";
}

if (isset($_GET['tbname'])) {
    $tblink = $dblink . "&tbname=" . $_GET['tbname'];
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
    $columns .= "</table>";
    $columns .= "<br /><p><a href='" . $tblink . "&list=yes'>Listar linhas</a></p>";
} else {
    $tblink = $dblink;
    $columns = "";
}

if (isset($_GET['list'])) {
    if ($_GET['list'] == 'yes') {
        $tbdata = "<table cellpadding='6'>";
        if (isset($_GET['offset'])) {
            $offset = "OFFSET " . $_GET['offset'];
            $start_row = $_GET['offset'] + 1;
        } else {
            $offset = "";
            $start_row = 1;
        }
        $sql = "SELECT * FROM `" . $_GET['tbname'] . "` LIMIT 20 $offset";
        $res = $mysqli->query($sql);
        $count_rows = 0;
        $continue = "";
        if ($res->num_rows > 0) {
            $hasHeader = False;
            while ($row = $res->fetch_assoc()) {
                $count_rows++;
                if (!$hasHeader) {
                    $tbdata_firstline = "<tr><td style='text-align: right;'>" . ($start_row + $count_rows - 1) . "</td>";
                    $tbdata .= "<tr><th>#</th>";
                    foreach($row as $key => $value) {
                        $tbdata .= "<th>$key</th>";
                        $tbdata_firstline .= "<td>$value</td>";
                    }
                    $tbdata .= "</tr>";
                    $tbdata_firstline .= "</tr>";
                    $tbdata .= $tbdata_firstline;
                    $hasHeader = True;
                } else {
                    $tbdata .= "<tr><td style='text-align: right;'>" . ($start_row + $count_rows - 1) . "</td>";
                    foreach($row as $key => $value) {
                        $tbdata .= "<td>$value</td>";
                    }
                    $tbdata .= "</tr>";
                }
            }
            $continue = "<p>";
            if ($start_row > 0) {
                if ($start_row > 20) {
                    $page_offset = $start_row - 21;
                    $continue .= "<a href='" . $tblink . "&list=yes&offset=$page_offset'>Anterior</a>&nbsp;&nbsp;&nbsp;";
                }
            } 
            if ($count_rows == 20) {
                $page_offset = $start_row + 19;
                $continue .= "<a href='" . $tblink . "&list=yes&offset=$page_offset'>Seguinte</a>&nbsp;&nbsp;&nbsp;";
            }
            $continue .= "</p>";
        }
        $tbdata .= "</table>";
        if ($count_rows > 0) {
            $tbdata = "<h2>Tabela " . $_GET['tbname'] . "</h2><h3>Linhas de " . $start_row . " a " . ($start_row + $count_rows - 1) . "</h3>" . $tbdata;
        } else {
            $tbdata = "<h2>Tabela " . $_GET['tbname'] . "</h2>";
            $tbdata .= "<p>Nenhuma linha encontrada.</p>";
        }
        $tbdata .= $continue;

    } else {
        $tbdata = "";
    }
} else {
    $tbdata = "";
}


$content .= "<div class='row'>";
$content .= "<div class='col-2'>$databases</div>";
$content .= "<div class='col-3'>$tables</div>";
$content .= "<div class='col-7'>$columns</div>";
$content .= "</div>";

if ($tbdata != "") {
    $content .= "<p>&nbsp;</p>";
    $content .= "<div class='row'>";
    $content .= "<div class='col-12'>$tbdata</div>";
    $content .= "</div>";
}

$template = new Template('db.index');
$data = array(
    'title' => 'DB',
    'content' => $content,
    'databases' => $arr_databases,
    'dbname' => (isset($_GET['dbname']) ? $_GET['dbname'] : ''),
    'tables' => $arr_tables
);
$template->display($data);
