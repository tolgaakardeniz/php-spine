/**
 * Sayfa yüklendikten sonra
 * After the page loads 
 * */
$(function() {
    'use strict';

    var timeOut = null;

    $(document).scroll((e) => {
        clearTimeout(timeOut);
        timeOut = setTimeout(() => {
            if (($(window).scrollTop() > 360) && ($(window).width() < 768)) {
                $(".profile-image").css("height", "128px");
                $(".profile-image > div:last-child").addClass("profile-menu");
            } else {
                $(".profile-image").removeAttr("style", "");
                $(".profile-image > div:last-child").removeClass("profile-menu");
            }
        }, 50);
    });

    $(window).resize((e) => {
        clearTimeout(timeOut);
        timeOut = setTimeout(() => {
            if ($(window).width() >= 769) {
                $(".profile-image").removeAttr("style", "");
                $(".profile-image > div:last-child").removeClass("profile-menu");
            } else {
                if (($(window).scrollTop() > 360) && ($(window).width() < 768)) {
                    $(".profile-image").css("height", "128px");
                    $(".profile-image > div:last-child").addClass("profile-menu");
                }
            }
        }, 50);
    });

    /**
     * Profil fotoğrafını değiştirme simgesine tıkladığında
     */
    $("#profilePhoto").on("click", () => {
        /**
         * Profil fotoğrafı yükleme dosya inputunu tetikle
         */
        $("#profileImageFile").trigger("click");
    });

    /**
     * Profil fotoğrafı yükleme dosya inputunu değiştiğinde
     */
    $('#profileImageFile').change(() => {
        /**
         * Profil fotoğrafı yükleme formunu gönder
         */
        $('#profileImageForm').submit();
    });

    /**
     * Arkaplan fotoğrafını değiştirme simgesine tıkladığında
     */
    $("#profileBackImage").on("click", () => {
        /**
         * Arkaplan fotoğrafı yükleme dosya inputunu tetikle
         */
        $("#profileBackImageFile").trigger("click");
    });

    /**
     * Arkaplan fotoğrafı yükleme dosya inputunu değiştiğinde
     */
    $('#profileBackImageFile').change(() => {
        /**
         * Arkaplan fotoğrafı yükleme formunu gönder
         */
        $('#profileBackImageForm').submit();
    });
});

/**
 * Fotoğraf gönderme işlevi yapar
 * 
 * Tolga AKARDENİZ
 * 21.05.2021 00:19
 * 
 * @param {object} x 
 */
function imageForm(o) {
    try {
        var f = new FormData(o[0]);

        $.ajax({
            url: pageUrl,
            type: 'POST',
            data: f,
            async: false,
            success: function(r, s, x) {
                console.log(r, s, x);
                /**
                 * Gönderim sonucu geldiğinde
                 */
                let a = null;

                /* Sonuc Json mu kontrol et */
                try { a = x.responseJSON; } catch (e) { a = null; }

                try {
                    /** Oturum kapanmışsa sayfayı yeniler */
                    statusCode(x, '/kullanici/giris/');

                    if ((typeof a !== "object") || (a === null)) {
                        $("html")[0].innerHTML = r;
                    } else {
                        if (a.status === true) {
                            window.location = a.redirect;
                        } else {
							w.alert('Uyarı', 'Bir sorun oluştu lütfen tekrar deneyin.', 'error');
                        }
                    }
                } catch (e) {
                    console.log(e.message);
                }
            },
            cache: false,
            contentType: false,
            processData: false,
            error: function(r) {
                /**
                 * İşlem başarısız olursa hataları göster
                 */
                statusCode(r);
				/**
				 * Dosya gönderme inputunu boşalt
				 */
				$(o).find('[name="file"]').val(null);
            }
        });
    } catch (e) {
        console.log(e.message);
    }

    return false;
}