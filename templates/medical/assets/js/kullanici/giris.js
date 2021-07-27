$(function() {
    'use strict';

    /**
     * sweetaler2 body yüksekliğini auto yapıyor bunu engellemek için kullanıyoruz
     */
    $('body').attr('style', 'height: 100% !important');

    /**
     * Form gönderilmek istenildiğinde KullaniciAdi'ni yeniden tanımla
     */
    $('#Oturum').on('submit', function(e) {
        /**
         * Kullanıcı adını sakla
         */
        w.write('KullaniciAdi', $('#KullaniciAdi').val());

        /**
         * Submit'i durdur
         */
        e.preventDefault();

        /**
         * Post işlemleri
         */
        $.post("/kullanici/giris/", {
            "Islem": "Giris",
            "Parola": $('#Parola').val(),
            "KullaniciAdi": $('#KullaniciAdi').val(),
            "Parola": $('#Parola').val(),
            "BeniHatirla": $('#BeniHatirla').prop('checked'),
            "Tur": "json"
        }).done(function(r, s, x) {
            /**
             * Gönderim sonucu geldiğinde
             */
            let a = null;

            /* Sonuc Json mu kontrol et */
            try { a = x.responseJSON; } catch (e) { a = null; }

            if ((typeof a !== "object") || (a === null)) {
				$("html")[0].innerHTML = r;
            } else {
                if (a.status === true) {
                    window.location = a.redirect;
                } else {
					w.alert('Uyarı', 'Bir sorun oluştu lütfen tekrar deneyin.', 'error');
				}
            }
        }).fail(function(r) {
            /**
             * İşlem başarısız olursa hataları göster
             */
            statusCode(r, (function() { $('#KullaniciAdi').trigger("focus").trigger("select"); }));
        });
    });

    /**
     * Tanımlı KullaniciAdi'nı forma yaz
     */
    let x = w.read('KullaniciAdi');

    if (x !== null) {
        $('#KullaniciAdi').val(x);
    }

    /**
     * Cookie'den BeniHatirla bilgisini al
     */
    let y = w.getCookie('BeniHatirla');

    /**
     * BeniHatirla kontrolleri
     */
    if (y !== null) {
        /**
         * Varsa
         */
        x = true;
        $('#Parola').val(y);
    } else {
        /**
         * Yoksa
         */
        x = false;
    }

    /**
     * Varsa yada yoksa BeniHatirla'nın durumunu değiştir
     */
    $('#BeniHatirla').prop('checked', x);

    if (x === true) {
        $('#Oturum').trigger("submit");
    }

    /**
     * KullaniciAdi alanına git
     */
    $('#KullaniciAdi').trigger("focus").trigger("select");
});