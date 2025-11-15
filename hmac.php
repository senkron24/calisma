<?php
include "session.php";
include "functions.php";

if (!$rPermissions["is_admin"]) {
    exit("Bu sayfaya erişim yetkiniz yok.");
}

if (!hasPermissions("adv", "settings") && !hasPermissions("adv", "database")) {
    exit("Bu sayfaya erişim yetkiniz yok.");
}

// HMAC Key üretme fonksiyonu
function generateHMACKey() {
    return bin2hex(random_bytes(16)); // 128-bit key (32 karakter)
}

// Yeni bir HMAC key ekleme
if (isset($_POST['create'])) {
    $hmac_key = $_POST['hmac_key'];
    $description = $rPurifier->purify($_POST['description']);

    $stmt = $db->prepare("INSERT INTO hmac_keys (hmac_key, description) VALUES (?, ?)");
    $stmt->bind_param('ss', $hmac_key, $description);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Yeni HMAC key oluşturuldu!";
    } else {
        echo "HMAC key oluşturulamadı!";
    }
    $stmt->close();
}

// Bir HMAC key'i yeniden oluşturma
if (isset($_POST['regenerate'])) {
    $id = intval($_POST['id']);
    $new_hmac_key = generateHMACKey();

    $stmt = $db->prepare("UPDATE hmac_keys SET hmac_key = ? WHERE id = ?");
    $stmt->bind_param('si', $new_hmac_key, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "HMAC key yeniden oluşturuldu!";
    } else {
        echo "HMAC key yeniden oluşturulamadı!";
    }
    $stmt->close();
}

// Mevcut HMAC key'leri listeleme
$hmac_keys = [];
$result = $db->query("SELECT * FROM hmac_keys");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $hmac_keys[] = $row;
    }
    $result->free();
}

// Rastgele bir HMAC Key oluşturmak için AJAX ile kullanılacak fonksiyon
if (isset($_POST['generate_random_key'])) {
    echo generateHMACKey();
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>HMAC Key Yönetimi</title>
    <link rel="stylesheet" href="styles.css"> <!-- Varsayılan stil dosyanızı buraya bağlayabilirsiniz -->
    <script>
        function generateRandomKey() {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById("hmac_key").value = xhr.responseText;
                }
            };
            xhr.send("generate_random_key=true");
        }
    </script>
</head>
<body>
    <h1>HMAC Key Yönetimi</h1>

    <h2>Yeni HMAC Key Oluştur</h2>
    <form method="post">
        <label for="description">Açıklama:</label>
        <input type="text" id="description" name="description" required><br><br>

        <label for="hmac_key">HMAC Key:</label>
        <input type="text" id="hmac_key" name="hmac_key" readonly><br><br>
        <button type="button" onclick="generateRandomKey()">Rastgele HMAC Key Oluştur</button><br><br>

        <button type="submit" name="create">Oluştur</button>
    </form>

    <h2>Mevcut HMAC Key'ler</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>HMAC Key</th>
            <th>Açıklama</th>
            <th>Oluşturulma Tarihi</th>
            <th>Güncellenme Tarihi</th>
            <th>İşlemler</th>
        </tr>
        <?php foreach ($hmac_keys as $key): ?>
        <tr>
            <td><?php echo $key['id']; ?></td>
            <td><?php echo $key['hmac_key']; ?></td>
            <td><?php echo $key['description']; ?></td>
            <td><?php echo $key['created_at']; ?></td>
            <td><?php echo $key['updated_at']; ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $key['id']; ?>">
                    <button type="submit" name="regenerate">Yeniden Oluştur</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
