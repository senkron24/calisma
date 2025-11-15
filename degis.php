<?php
include "session.php";
include "functions.php";
if (!$rPermissions["is_admin"] || !hasPermissions("adv", "block_uas")) {
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



$config_file = "/home/xtreamcodes/iptv_xtream_codes/nginx/conf/admin_panel.conf";
$error = "";
$success = "";
$random_number = "";

if (isset($_POST['new_number'])) {
    $new_number = $_POST['new_number'];
    $random_number = $new_number;

    // Guvenlik kontrolleri
    if (preg_match('/^[A-Za-z0-9]+$/', $new_number)) { // Yeni numara yalnizca alfanumerik karakterler icerir
        if (file_exists($config_file) && is_writable($config_file)) { // Konfigurasyon dosyasi mevcut ve yazilabilir
            $config_content = file_get_contents($config_file);
            $new_content = preg_replace('/location\s+\^~\s+\/[A-Za-z0-9]+\s+\{/m', "location ^~ /{$new_number} {", $config_content);
            if (file_put_contents($config_file, $new_content) !== false) { // Dosya basariyla guncellendi
                $success = "Konfigurasyon dosyasi basariyla guncellendi!";
                
                // Nginx'i yeniden baslat
                $output = shell_exec('sudo /home/xtreamcodes/iptv_xtream_codes/nginx/sbin/nginx -s reload');
                if ($output === null) {
                    $error = "Nginx yeniden baslatilamadi";
                }
            } else {
                $error = "Dosya guncellenemedi";
            }
        } else {
            $error = "Dosya bulunamadi veya yazilabilir degil";
        }
    } else {
        $error = "Gecersiz numara formati";
    }
} else {
    $random_number = generateRandomString(13);
}

function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

?>

<!DOCTYPE html>
<html>
<head>
  <title>Access Code Degistir</title>
</head>
<body>
  <h1>Access Code Degistir</h1>
  <?php if ($error !== ""): ?>
    <div style="color:red;"><?php echo $error; ?></div>
  <?php elseif ($success !== ""): ?>
    <div style="color:green;"><?php echo $success; ?></div>
  <?php endif; ?>
  <form method="POST">
    <label for="new_number">Yeni Kod :  </label>
    <input type="text" name="new_number" value="<?php echo $random_number; ?>" required>
    <br><br>
    <button type="submit">Kayit et</button>
    <button type="button" onclick="location.href='';">Oto Kod</button>
  </form>
</body>
</html>
