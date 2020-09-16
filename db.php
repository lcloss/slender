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

// Populate databases
$db_list = mysqli_query($mysqli, "SHOW DATABASES");
$arr_databases = [];
foreach($db_list as $db_name) {
    $arr_databases[] = array(
        'value' => $db_name['Database'],
        'caption' => $db_name['Database'],
        'selected' => ($db_name['Database'] == (!empty($_GET['dbname']) ? $_GET['dbname'] : '') ? true : false)
    );
}

// Populate tables
$arr_tables = [];
if (!empty($_GET['dbname'])) {
    $dblink = $link . "?dbname=" . $_GET['dbname'];
    $mysqli->select_db($_GET['dbname']);
    $tb_list = mysqli_query($mysqli, "SHOW TABLES");

    $key = "Tables_in_" . $_GET['dbname'];
    foreach($tb_list as $tb_name) {
        $arr_tables[] = array(
            'value' => $tb_name[$key],
            'caption' => $tb_name[$key],
            'link_structure' => $dblink . "&tbname=" . $tb_name[$key]  . "&op=struct",
            'link_data' => $dblink . "&tbname=" . $tb_name[$key] . "&op=list",
            'selected' => ($tb_name[$key] == (!empty($_GET['tbname']) ? $_GET['tbname'] : '') ? true : false)
        );
    }
} else {
    $dblink = $link;
}

// Populate structure
$arr_columns = array();
if (!empty($_GET['tbname'])) {
    if (!empty($_GET['op'])) {
        if ($_GET['op'] == 'struct') {
            $tblink = $dblink . "&tbname=" . $_GET['tbname'];
            $col_list = mysqli_query($mysqli, "SHOW COLUMNS FROM " . $_GET['tbname']);

            $key = "Tables_in_" . $_GET['dbname'];
            $column_no = 0;
            foreach($col_list as $col_name) {
                $column_no++;
                $arr_columns[] = array(
                    'id' => $column_no,
                    'name' => $col_name['Field'],
                    'type' => $col_name['Type'],
                    'null' => $col_name['Null'],
                    'key' => $col_name['Key'],
                    'default' => $col_name['Default']
                );
            }
        } else {
            $tblink = $dblink;
        }
    } else {
        $tblink = $dblink;
    }
} else {
    $tblink = $dblink;
}

// Populate data
$tbheader = array();
$tbdata = array();
$tbindex = "";

if (!empty($_GET['tbname'])) {
    if (!empty($_GET['op'])) {
        if ($_GET['op'] == 'list') {

            if (!empty($_GET['offset'])) {
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
                        $tbheader[] = array(
                            'column' => '#'
                        );
                        $col_data = array(
                            'id' => $start_row + $count_rows - 1
                        );
                        foreach($row as $key => $value) {
                            $tbheader[] = array(
                                'column' => $key
                            );
                            $col_data[$key] = $value;
                        }
                        $tbdata[] = array(
                            'columns' => array(
                                'column' => $col_data
                            )
                        );
                        $hasHeader = True;
                    } else {
                        $col_data = array(
                            'id' => $start_row + $count_rows - 1
                        );
                        foreach($row as $key => $value) {
                            $col_data[$key] = $value;
                        }
                        $tbdata[] = array(
                            'columns' => array(
                                'column' => $col_data
                            )
                        );
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
            if ($count_rows > 0) {
                $tbindex = "Linhas de " . $start_row . " a " . ($start_row + $count_rows - 1);
            } else {
                $tbindex .= "Nenhuma linha encontrada.";
            }
        }
    }
}


$template = new Template('db.index');
$data = array(
    'title' => 'DB',
    'content' => $content,
    'databases' => $arr_databases,
    'dbname' => (!empty($_GET['dbname']) ? $_GET['dbname'] : ''),
    'tables' => $arr_tables,
    'tbname' => (!empty($_GET['tbname'])? $_GET['tbname'] : ''),
    'columns' => $arr_columns,
    'op' => (!empty($_GET['op'])? $_GET['op'] : ''),
    'tbheader' => $tbheader,
    'tbindex' => $tbindex,
    'tbdata' => $tbdata
);
$template->display($data);
