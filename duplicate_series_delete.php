<?php
include "session.php";
include "functions.php";
if (!$rPermissions["is_admin"]) {
    exit;
}
if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
if ($rSettings["sidebar"]) {
    echo "        <div class=\"content-page\"><div class=\"content boxed-layout-ext\"><div class=\"container-fluid\">\n        ";
} else {
    echo "        <div class=\"wrapper boxed-layout-ext\"><div class=\"container-fluid\">\n        ";
}

// SQL sorgusunu çalıştırmak için fonksiyon
function execute_query() {
    global $db;
    $query = "DELETE FROM streams
              WHERE (id, type, stream_source) NOT IN (
                  SELECT * FROM (
                      SELECT MAX(id) AS id, type, stream_source
                      FROM streams
                      WHERE type = 5
                      GROUP BY stream_source
                  ) AS subquery
              ) AND type = 5;";

    $db->query($query);
}

// Butona basıldığında sorguyu çalıştır
if (isset($_POST["run_query"])) {
    execute_query();
}
?>

<!-- HTML formu -->
<form method="post">
    <button type="submit" name="run_query" class="btn btn-primary">Sorguyu Çalıştır</button>
</form>

<?php
if ($rSettings["sidebar"]) {
    echo "</div></div></div>\n";
} else {
    echo "</div></div>\n";
}
include "footer.php";
?>
