<?php
include "session.php";
include "functions.php";

// Admin yetkisi kontrolü
if (!$rPermissions["is_admin"]) {
    exit("Erişim yetkiniz yok.");
}

$current_url = htmlspecialchars($_SERVER['PHP_SELF']);

// --- AKILLI OTOMATİK DÜZELTME İŞLEMİ (Smart Fix) ---
// Sizin istediğiniz mantık burada çalışır:
// 1. Aynı URL ise -> SİL
// 2. URL farklı, isim aynı ise -> NOKTA KOY
if (isset($_GET['smart_fix']) && $_GET['smart_fix'] == "1") {
    
    $deleted_count = 0;
    $renamed_count = 0;

    // 1. ADIM: Tüm tekrarlanan isimleri çek
    $sql = "SELECT id, stream_display_name, stream_source, COUNT(stream_display_name) as stream_count 
            FROM streams 
            WHERE type = 2 
            GROUP BY stream_display_name 
            HAVING stream_count > 1";
            
    $result = $db->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        $current_name = $row['stream_display_name'];

        // Bu isme ait tüm kayıtları detaylı çek
        $stmt = $db->prepare("SELECT id, stream_source FROM streams WHERE stream_display_name = ? AND type = 2");
        $stmt->bind_param("s", $current_name);
        $stmt->execute();
        $detail_result = $stmt->get_result();
        
        $source_groups = [];
        
        // Kayıtları URL'lerine göre grupla
        while ($d_row = $detail_result->fetch_assoc()) {
            $source_groups[$d_row['stream_source']][] = $d_row['id'];
        }
        $stmt->close();

        // --- MANTIK 1: AYNI URL OLANLARI SİL ---
        $remaining_ids = []; // Silme işleminden sağ kurtulan ID'ler
        
        foreach ($source_groups as $source => $ids) {
            if (count($ids) > 1) {
                // Aynı URL'den birden fazla var.
                // ID'leri büyükten küçüğe sırala (En yeni ID en başa gelir)
                rsort($ids);
                
                // En yeni ($ids[0]) kalsın, diğerlerini sil
                $keep_id = $ids[0];
                $remaining_ids[] = $keep_id; // Bu ID yaşamaya devam edecek
                
                for ($i = 1; $i < count($ids); $i++) {
                    $delete_id = $ids[$i];
                    $del_stmt = $db->prepare("DELETE FROM streams WHERE id = ?");
                    $del_stmt->bind_param("i", $delete_id);
                    if ($del_stmt->execute()) {
                        $deleted_count++;
                    }
                    $del_stmt->close();
                }
            } else {
                // Bu URL'den sadece 1 tane var, silmeye gerek yok.
                $remaining_ids[] = $ids[0];
            }
        }

        // --- MANTIK 2: KALANLARIN URL'LERİ FARKLI AMA İSİMLERİ HALA AYNI ---
        // $remaining_ids dizisinde artık her URL'den sadece 1 tane temsilci var.
        // Eğer bu dizide hala 1'den fazla kayıt varsa, demek ki URL'ler farklı ama İsim aynı.
        
        if (count($remaining_ids) > 1) {
            // İlk kayıt ($remaining_ids[0]) ismini korusun.
            // Diğerlerinin sonuna nokta koyalım.
            for ($i = 1; $i < count($remaining_ids); $i++) {
                $rename_id = $remaining_ids[$i];
                
                // Mevcut ismin sonuna nokta ekle
                $new_name_update = $current_name . "."; 
                
                // Veritabanında güncelle
                $up_stmt = $db->prepare("UPDATE streams SET stream_display_name = ? WHERE id = ?");
                $up_stmt->bind_param("si", $new_name_update, $rename_id);
                if ($up_stmt->execute()) {
                    $renamed_count++;
                }
                $up_stmt->close();
            }
        }
    }

    header("Location: $current_url?status=smart_fixed&del=$deleted_count&ren=$renamed_count");
    exit;
}

// --- Tekil Silme İşlemi ---
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $db->query("DELETE FROM streams WHERE id = $id");
    header("Location: $current_url?status=deleted");
    exit;
}

// --- GÖRÜNTÜLEME LİSTESİ SORGUSU ---
// Sayfa açılışında listeyi doldurmak için
$array = [];
$sql = "SELECT id, stream_display_name, stream_source, COUNT(stream_display_name) as stream_count 
        FROM streams 
        WHERE type = 2 
        GROUP BY stream_display_name 
        HAVING stream_count > 1 
        ORDER BY id DESC";
$result = $db->query($sql);

while ($row = $result->fetch_assoc()) {
    // Detayları al
    $sub_sql = "SELECT id, stream_source FROM streams WHERE stream_display_name = '".$db->real_escape_string($row['stream_display_name'])."' AND type=2";
    $sub_res = $db->query($sub_sql);
    
    $ids = [];
    $sources = [];
    $has_same_source = false;
    
    $source_check = [];
    
    while($sub = $sub_res->fetch_assoc()) {
        $ids[] = $sub['id'];
        $sources[] = $sub['stream_source'];
        
        if(in_array($sub['stream_source'], $source_check)) {
            $has_same_source = true; // Aynı URL tespit edildi
        }
        $source_check[] = $sub['stream_source'];
    }
    
    $array[] = [
        'name' => $row['stream_display_name'],
        'ids' => $ids,
        'sources' => $sources,
        'has_same_source' => $has_same_source
    ];
}

// Header
if ($rSettings["sidebar"]) { include "header_sidebar.php"; echo "<div class=\"content-page\"><div class=\"content boxed-layout\"><div class=\"container-fluid\">"; } 
else { include "header.php"; echo "<div class=\"wrapper boxed-layout\"><div class=\"container-fluid\">"; }
?>

<div class="row mt-3">
    <div class="col-12">
        <?php if(isset($_GET['status'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                if($_GET['status'] == 'smart_fixed') {
                    echo "<strong>İşlem Tamamlandı!</strong><br>";
                    echo "Silinen (Aynı URL): " . intval($_GET['del']) . " adet.<br>";
                    echo "İsmi Değiştirilen (Farklı URL): " . intval($_GET['ren']) . " adet.";
                }
                if($_GET['status'] == 'deleted') echo "Kayıt silindi.";
                ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <div class="page-title-box">
            <div class="page-title-right">
                <?php if(count($array) > 0): ?>
                <a href="?smart_fix=1" onclick="return confirm('Bu işlem şunları yapacak:\n1. URL\'si aynı olanların kopyalarını SİLECEK.\n2. URL\'si farklı olanların ismine NOKTA koyacak.\n\nOnaylıyor musunuz?');">
                    <button type="button" class="btn btn-success waves-effect waves-light">
                        <i class="mdi mdi-auto-fix"></i> Otomatik Temizle ve Düzelt
                    </button>
                </a>
                <?php endif; ?>
            </div>
            <h4 class="page-title">Duplicate Movies (<?php echo count($array); ?> İsim Çakışması)</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="datatable" class="table table-hover dt-responsive nowrap">
                    <thead>
                        <tr>
                            <th>Film Adı</th>
                            <th>Durum</th>
                            <th>Kayıtlar (ID - Kaynak)</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($array as $item): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                            <td>
                                <?php if($item['has_same_source']): ?>
                                    <span class="badge badge-danger">Aynı URL (Silinmeli)</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Farklı URL (İsim Değişmeli)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <ul style="list-style:none; padding-left:0; margin-bottom:0; font-size:12px;">
                                <?php 
                                for($k=0; $k<count($item['ids']); $k++): 
                                    $color = "text-muted";
                                    // URL'leri kontrol edip görsellik katabiliriz
                                ?>
                                    <li class="<?php echo $color; ?>">
                                        ID: <strong><?php echo $item['ids'][$k]; ?></strong> - 
                                        <?php echo substr($item['sources'][$k], 0, 50) . "..."; ?>
                                    </li>
                                <?php endfor; ?>
                                </ul>
                            </td>
                            <td>
                                <a href="?delete_id=<?php echo $item['ids'][0]; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Sadece bu kaydı silmek istiyor musunuz?');">Tek Sil</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
if ($rSettings["sidebar"]) { echo "</div></div></div>"; } else { echo "</div></div>"; }
?>

<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 copyright text-center"><?php echo getFooter(); ?></div>
        </div>
    </div>
</footer>
<script src="assets/js/vendor.min.js"></script>
<script src="assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables/dataTables.bootstrap4.js"></script>
<script src="assets/js/app.min.js"></script>
<script>
    $(document).ready(function() {
        $("#datatable").DataTable();
    });
</script>
</body>
</html>