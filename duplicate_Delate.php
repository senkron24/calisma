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
$query = "SELECT id, category_name FROM stream_categories";
$result = $db->query($query);

$categories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

if (isset($_POST["submit"])) {
    $category_id = intval($_POST["category_id"]);

    $subquery = "SELECT MAX(id) AS id, category_id, stream_display_name
                 FROM streams
                 WHERE category_id = $category_id
                 GROUP BY stream_display_name";
    
    $delete_query = "DELETE FROM streams
                     WHERE (id, category_id, stream_display_name) NOT IN (
                         SELECT * FROM ($subquery) AS subquery
                     ) AND category_id = $category_id";
    
    $delete_result = $db->query($delete_query);
    
    if ($delete_result) {
        echo "Sorgu başarıyla çalıştırıldı.";
    } else {
        echo "Sorgu çalıştırılırken bir hata oluştu.";
    }
}

?>

<form method="post">
    <label for="category_id">Kategori Seçin:</label>
    <select name="category_id" id="category_id">
        <?php foreach ($categories as $category): ?>
            <option value="<?= $category["id"] ?>"><?= $category["category_name"] ?></option>
        <?php endforeach; ?>
    </select>
    <input type="submit" name="submit" value="Duplicate Olanlari Sil">
</form>
