# WordPress Telegram Yorum Bildirimi

WordPress sitenize yeni bir yorum yapıldığında yöneticiye Telegram üzerinden bildirim gönderir. Eklenti, WordPress'in **Ayarlar → Telegram yorum bildirimi** sayfasından yapılandırılır.

## Kurulum ve yapılandırma

1. `wordpress-telegram-yorum-eklentisi.php` dosyasını `wp-content/plugins/wordpress-telegram-yorum-eklentisi/` dizinine yükleyin ve WordPress yönetim panelinden eklentiyi etkinleştirin.
2. Telegram'da BotFather ile bir bot oluşturup bot tokenını alın.
3. Botu hedef sohbete ekleyin ve sohbet kimliğini öğrenin. Eklenti sayısal sohbet kimliklerini ve `@kanal_kullanici_adi` biçimindeki kanal adlarını kabul eder.
4. WordPress'te **Ayarlar → Telegram yorum bildirimi** sayfasına bot tokenı ve sohbet kimliğini girip kaydedin.

Token ayarlar sayfasında tekrar gösterilmez. Mevcut tokenı değiştirmeden ayarları güncellemek için token alanını boş bırakın. Yorum bildirimi; yazar, yazı başlığı, yorum durumu ve yönetim panelindeki inceleme bağlantısını içerir. Spam veya çöp olarak işaretlenmiş yorumlar gönderilmez.

## Erişilebilirlik

Ayarlar WordPress'in standart Settings API'siyle oluşturulur; alanların görünür etiketleri, açıklamaları ve parola alanı için ekran okuyucu açıklaması vardır. Token düz metin olarak gösterilmez.
