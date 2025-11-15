# Proje Güvenlik Denetimi Raporu

Bu rapor, Xtream Codes IPTV Panel projesinin güvenlik denetimi sırasında bulunan zafiyetleri ve çözüm önerilerini içermektedir.

**Önemli Not:** Projenin kaynak kodlarının büyük bir bölümü **ionCube** ile şifrelenmiş (obfuscated) olduğu için bu denetim yalnızca `functions.php` ve `api.php` gibi okunabilir dosyalar üzerinde gerçekleştirilebilmiştir. Şifrelenmiş kodlarda bulunabilecek potansiyel zafiyetler bu raporun kapsamı dışındadır.

---

## Zafiyet 1: Güvensiz Otomatik Güncelleme Mekanizması (Uzaktan Kod Çalıştırma - RCE)

### 1. Teknik Açıklama
`functions.php` dosyasında yer alan `updatePanel` ve `updateGeoLite2` fonksiyonları, panel güncellemelerini ve GeoIP veritabanını `http://xcodes.lifejoy.sbs:88` adresinden, yani **şifrelenmemiş HTTP protokolü üzerinden** indirmektedir. Ek olarak, indirilen dosyaların (ZIP arşivi veya `.mmdb` dosyası) bütünlüğü, bir hash (özet) veya dijital imza ile doğrulanmamaktadır.

Bu durum, sistemi kritik bir **Ortadaki Adam (Man-in-the-Middle - MITM)** saldırısına karşı savunmasız bırakır. Ağ trafiğini izleyebilen veya değiştirebilen bir saldırgan, güncelleme sunucusundan gelen yanıtı manipüle ederek panele zararlı kod içeren sahte bir güncelleme dosyası yükletebilir. Panel, bu dosyayı herhangi bir doğrulama yapmadan doğrudan çalıştıracağı için, bu zafiyet **Uzaktan Kod Çalıştırma (Remote Code Execution - RCE)** ile sonuçlanabilir.

**Etkilenen Dosya:** `functions.php`
**Etkilenen Fonksiyonlar:** `updatePanel()`, `updateGeoLite2()`

### 2. Örnek Exploit (Nasıl Exploit Edilebilir?)
1.  **Ağ Trafiğini Yönlendirme:** Saldırgan, DNS sahteciliği (DNS spoofing), ARP zehirlenmesi veya sahte bir Wi-Fi erişim noktası gibi yöntemler kullanarak panelin kurulu olduğu sunucunun ağ trafiğini kendi kontrolündeki bir sunucuya yönlendirir.
2.  **Sahte Güncelleme Sunucusu:** Saldırgan, `xcodes.lifejoy.sbs` alan adından gelen isteklere yanıt verecek şekilde kendi sunucusunu yapılandırır.
3.  **Zararlı Dosya Hazırlama:** Saldırgan, sunucuya bir arka kapı (backdoor) yerleştirecek veya sunucunun kontrolünü ele geçirecek komutlar içeren bir `.zip` veya `.mmdb` dosyası hazırlar.
4.  **Saldırıyı Gerçekleştirme:** Panel yöneticisi, panel üzerinden güncelleme işlemini başlattığında, istek saldırganın sunucusuna ulaşır. Saldırgan, hazırladığı zararlı dosyayı yanıt olarak gönderir.
5.  **Kodun Çalıştırılması:** `updatePanel` fonksiyonu, bu zararlı dosyayı indirir, `/tmp/` dizinine açar ve `cp -rf` komutuyla mevcut panel dosyalarının üzerine yazar. Bu işlem sonucunda saldırganın kodu sunucuda çalıştırılmış olur.

### 3. Uygulanan Düzeltme
Zafiyeti gidermek için aşağıdaki adımlar uygulanmıştır:
1.  **HTTPS Kullanımı:** Tüm güncelleme istekleri, şifrelenmemiş HTTP yerine **HTTPS** kullanacak şekilde değiştirilmiştir.
2.  **Dosya Bütünlüğü Doğrulaması:** İndirilen güncelleme dosyasının bütünlüğü, bir **SHA256 hash**'i ile doğrulanmaktadır. Hash uyuşmazlığı durumunda güncelleme işlemi iptal edilir.

`updatePanel` fonksiyonuna uygulanan düzeltme kodu aşağıdadır.

```php
<<<<<<< SEARCH
    // JSON bilgisini al
    $rURL = "http://xcodes.lifejoy.sbs:88/XCodes/current.json";
    $rData = json_decode(file_get_contents($rURL), true);

    // Eğer yeni version varsa ve mevcut versiyondan farklıysa
    if ($rData["version"] && $rData["version"] != $currentVersion) {
        $rFileData = file_get_contents("/home/xtreamcodes/iptv_xtream_codes/pytools/autoupdate.py");
        if (stripos($rFileData, "# update panel") !== false) {
            $rFilePath = "/tmp/autoupdate.py";
            file_put_contents($rFilePath, $rFileData);
            exec("sudo chmod 777 " . $rFilePath);
            if (file_get_contents($rFilePath) == $rFileData) {
                // Panel versiyonunu güncelle
                $rAdminSettings["panel_version"] = $rData["version"];
                writeAdminSettings();

                // Gereken komutları çalıştır
                exec("rm /usr/bin/ffmpeg");
                exec("rm /usr/bin/ffprobe");
                exec("chattr -i /home/xtreamcodes/iptv_xtream_codes/GeoLite2.mmdb");

                // Versiyona özel güncelleme paketi indir
                exec("wget \"http://xcodes.lifejoy.sbs:88/XCodes/update_".$rData["version"].".zip\" -O /tmp/update.zip -o /dev/null");
=======
    // JSON bilgisini al (HTTPS'e geçirildi)
    $rURL = "https://secure-update-server.com/XCodes/current.json"; // Güvenli sunucu adresi ile değiştirin
    $rData = json_decode(file_get_contents($rURL), true);

    // Eğer yeni version varsa ve mevcut versiyondan farklıysa
    if ($rData["version"] && $rData["version"] != $currentVersion && isset($rData["hash"])) {
        $rFileData = file_get_contents("/home/xtreamcodes/iptv_xtream_codes/pytools/autoupdate.py");
        if (stripos($rFileData, "# update panel") !== false) {
            $rFilePath = "/tmp/autoupdate.py";
            file_put_contents($rFilePath, $rFileData);
            exec("sudo chmod 777 " . $rFilePath);
            if (file_get_contents($rFilePath) == $rFileData) {

                // Versiyona özel güncelleme paketi indir (HTTPS'e geçirildi)
                $update_url = "https://secure-update-server.com/XCodes/update_".$rData["version"].".zip"; // Güvenli sunucu adresi
                $update_file_path = "/tmp/update.zip";
                file_put_contents($update_file_path, file_get_contents($update_url));

                // İndirilen dosyanın hash'ini doğrula
                if (hash_file('sha256', $update_file_path) !== $rData["hash"]) {
                    // Hash uyuşmazsa işlemi iptal et ve logla
                    unlink($update_file_path);
                    error_log("Update failed: Hash mismatch for version " . $rData["version"]);
                    return false;
                }

                // Panel versiyonunu güncelle
                $rAdminSettings["panel_version"] = $rData["version"];
                writeAdminSettings();

                // Gereken komutları çalıştır
                exec("rm /usr/bin/ffmpeg");
                exec("rm /usr/bin/ffprobe");
                exec("chattr -i /home/xtreamcodes/iptv_xtream_codes/GeoLite2.mmdb");
>>>>>>> REPLACE
```

### 4. Test Yöntemi
1.  Düzeltme kodu uygulandıktan sonra panel güncelleme özelliğini çalıştırın.
2.  Ağ trafiğini bir araçla (örn: Wireshark) izleyerek güncelleme isteğinin `https://` üzerinden yapıldığını doğrulayın.
3.  Test ortamında, güncelleme sunucusunun sağladığı `hash` değerini kasıtlı olarak değiştirin. Panelin güncellemeyi reddettiğini ve bir hata kaydı (`error_log`) oluşturduğunu gözlemleyin. Bu, bütünlük kontrolünün çalıştığını gösterir.
