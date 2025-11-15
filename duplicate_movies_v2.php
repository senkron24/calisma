<?php
// session.php ve functions.php dosyalarınızı dahil edin
include "session.php";
include "functions.php";

// Admin yetkisi kontrolü (isteğe bağlı, güvenlik için bırakılabilir)
if (!$rPermissions["is_admin"]) {
    die("Yetkisiz erişim.");
}

## 1. Mükerrer Kaynakları Toplama (Yavaş Adım, Mükerrer Kaynak URL'lerini Bulma)

// Bu sorgu, mükerrer olan tüm stream_source değerlerini getirir.
// Aynı zamanda sizin istisnalarınızı da (boş ve http) hariç tutar.
$sql = "SELECT stream_source, GROUP_CONCAT(id ORDER BY id DESC) as ids_to_check
        FROM streams
        WHERE stream_source != '[]'
        AND stream_source NOT LIKE '[\"http%'
        GROUP BY stream_source
        HAVING COUNT(stream_source) > 1";

$result = $db->query($sql);

if ($result === false) {
    die("Veritabanı sorgusu hatası: " . $db->error);
}

$deleted_count = 0;
$error_count = 0;

## 2. Parçalara Ayırarak Silme (Hızlı Adımlar)

while ($row = $result->fetch_assoc()) {
    $ids_string = $row['ids_to_check'];
    // ID'ler büyükten küçüğe sıralanmış dizedir (örn: "2050,2001,150")
    $ids = explode(',', $ids_string);

    // En yeni kaydı (en büyük ID'yi) KORUYORUZ.
    // PHP'de rsort (büyükten küçüğe sıralama) yapmıştık. GROUP_CONCAT(ORDER BY id DESC)
    // kullanarak bu ID'ler zaten sıralı gelir.
    
    // İlk ID (en büyük ID) korunacak orijinal kayıttır.
    $original_id = $ids[0]; 

    // Geri kalan ID'ler silinecek kopyalardır (i = 1'den başlarız)
    for ($i = 1; $i < count($ids); $i++) {
        $id_to_delete = (int) $ids[$i];
        
        // Tek tek silme sorgusu (Hızlıdır, zaman aşımı yapmaz)
        $delete_sql = "DELETE FROM streams WHERE id = " . $id_to_delete;
        
        if ($db->query($delete_sql)) {
            $deleted_count++;
        } else {
            $error_count++;
        }
    }
}

// Sonuç mesajı
$message = "Yerel dosya kopyalarını silme işlemi tamamlandı.\n";
$message .= "$deleted_count kayıt başarıyla silindi.\n";
if ($error_count > 0) {
    $message .= "$error_count kayıt silinemedi.";
}

// Tarayıcıya mesajı basar
echo "<script>alert('$message');</script>";
echo "<h1>İşlem Tamamlandı</h1>";
echo "<p>$deleted_count kayıt silindi. $error_count hata oluştu.</p>";

// Eğer bu betiği web arayüzünde çalıştırmak istemiyorsanız,
// php-cli üzerinden terminalde de çalıştırabilirsiniz:
// php delete_source_duplicates.php

?>