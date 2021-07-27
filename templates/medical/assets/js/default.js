/**
 * Fonksiyon Kütüphanesi
 */
const w = (function() {
    "use strict";

    /**
     * 
     * @param {string} n 
     * @param {"string|integer"} t
     */
    const write = function(n, t) {
        if (typeof Storage !== "undefined") {
            return localStorage.setItem(n, t);
        } else {
            return this.setCookie(n, t, 30);
        }
    };

    /**
     * 
     * @param {string} n 
     */
    const read = function(n) {
        if (typeof Storage !== "undefined") {
            return localStorage.getItem(n);
        } else {
            return this.getCookie(n);
        }
    };

    /**
     * 
     * @param {string} n 
     * 
     * return localStorage.removeItem | eraseCookie(n)
     */

    const remove = function(n) {
        if (typeof Storage !== "undefined") {
            return localStorage.removeItem(n);
        } else {
            return this.eraseCookie(n);
        }
    };


    /**
     * 
     * @param {"string name"} x 
     * @param {"string value"} y 
     * @param {"integer days"} d 
     */
    function setCookie(x, y, d) {
        var expires = "";

        if (d) {
            var date = new Date();
            date.setTime(date.getTime() + (d * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }

        document.cookie = x + "=" + (y || "") + expires + "; path=/;secure;";
    }

    /**
     * 
     * @param {string name} x 
     * @returns string
     */
    function getCookie(x) {
        let y = x + "=";

        let ca = document.cookie.split(';');

        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];

            while (c.charAt(0) == ' ') {
                c = c.substring(1, c.length);
            }

            if (c.indexOf(y) == 0) return c.substring(y.length, c.length);
        }

        return null;
    }

	/**
	 * 
	 * @param {"string for title"} y 
	 * @param {"string for html"} z 
	 * @param {"string warning, error, success, info, question"} q 
	 * @param {"integer for timer"} t 
	 * @param {"function for after process"} x 
	 */

	 const alert = function(y, z, q, t, x) {
		t = t || 2000;
		q = q || "success";
		return Swal.fire({
			icon: q,
			title: y,
			html: z,
			timer: t,
			confirmButtonText: "Tamam",
			customClass: defaultClass
		}).then((e) => {
			if (typeof x === "function") {
				setTimeout(function() { x(e); }, 500);
			}
		});
	};

    /**
     * Erase Cookie
     * 
     * @param {string} x
     */
    const eraseCookie = function(x) {
        document.cookie = x + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }

    return {
		'alert': alert,
        'write': write,
        'read': read,
        'remove': remove,
        'setCookie': setCookie,
        'getCookie': getCookie,
        'eraseCookie': eraseCookie
    };
}());


window.name = "MainWindow";

let p = window.location.pathname,
    a, l;
try {
    a = p.match(/(?:(?:[^\/]).+?(?=(?:\/|$)))/g, ""), l = p.match(/[^\/]+(?=\/$|$)/)[0], p = "/" + a[0] + "/";
} catch (Err) {
    a = [], p = p, l = p;
}

const pageOrginUrl = window.location.origin
const pageUrl = window.location.pathname;
const pageFirstUrl = p;
const pageLastUrl = l;
const pageUrlArray = a;

/**
 * Pencere Aktif
 */
var isTabActive;

window.onfocus = function() {
    isTabActive = true;
};

/* window.onmouseover = function() {
	 clearTimeout(Window["WindowTimeOut"]);
	 Window["WindowTimeOut"] = setTimeout(() => {
		 isTabActive = true;
		 console.log(isTabActive);
	 }, 250);
 }; */

window.onload = function() {
    isTabActive = true;
};

window.onblur = function() {
    isTabActive = false;
};

const defaultClass = {
    confirmButton: "btn green text-white p-2 px-3 rounded-pill",
    cancelButton: "btn red text-white p-2 px-3 rounded-pill"
};

/**
 * Browser theme color check and apply 
 * 
 * @param {event} e 
 */

$(function() {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        let o = document.getElementsByTagName("html")[0];

        if (e.matches) {
            o.className = "dark";
        } else {
            o.className = "light";
        }
    });

    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        let o = document.getElementsByTagName("html")[0];
        o.className = "dark";
    }

    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
        let o = document.getElementsByTagName("html")[0];
        o.className = "light";
    }

    /**
     * Screenfull
     */
    if (typeof screenfull != "undefined") {
        if (screenfull.isEnabled) {
            $(document).on(screenfull.raw.fullscreenchange, function() {
                if (screenfull.isFullscreen) {
                    $(".fa-expand").attr("data-original-title","Normal ekran").toggleClass("fa-expand fa-compress");
                    $("html").addClass("full-screen");
                } else {
                    $(".fa-compress").attr("data-original-title","Tam ekran").toggleClass("fa-expand fa-compress");
                    $("html").removeClass("full-screen");
                }
            });
        }

        $(".fa-expand, .fa-compress").on("click", function(e) {
            if (typeof screenfull != "undefined") {
                if (screenfull.isEnabled) {
                    screenfull.toggle();
                }
            }
        });
    } else {
        $(".fa-expand").remove();
    }

    /**
     * Tooltips
     */
    x = "function" == typeof $().tooltip ? $('[data-toggle="tooltip"]').tooltip() : null;

    /**
     * Waves init
     */
    if (typeof Waves != "undefined") {
        var config = {
            duration: 500,
            delay: 200
        };

        Waves.init(config);
    }
});




/**
 * Oturum kapanmışsa sayfayı yenile 
 * 
 * Tolga AKARDENİZ
 * 21.05.2021 00:19
 * 
 * @param {string} code 
 * @param {object} r 
 * @param {string} url 
 * @returns true|false
 */
var statusCode = function(r, url = null) {
    let a = null,
        x;

    /* Sonuc Json mu kontrol et */
    try { a = r.responseJSON["errors"]; } catch (e) { a = null; }

    try {
        code = r.status;
        if ((code === 401) || (code === 403)) {
            /** Oturum kapanmışsa sayfayı yeniler */
            window.location = "/kullanici/giris/";
            return;
        }

        x = "";

        if ((typeof(a) === "object") && (a !== null)) {
            $.each(a, function(k, v) { x = x + " " + (k + 1) + " : " + v + "<br />"; });
        } else {
            x = r.responseText;
        }

        if (code === 500) {
			if (url === null)
			{
				w.alert('Uyarı', x, 'error');
			} else if (typeof url === "function") {
				w.alert('Uyarı', x, 'error', 700000, url);
			} else {
				w.alert('Uyarı', x, 'error', 700000, (function() {window.location = url;}));
			}
        }
    } catch (e) {
        console.log(e)
    }
}











/**async işlemi */

async function toplaGel() {
    let formData = new FormData();

    formData.append('Islem', 'Filtreler');

    let sonuc = await fetch('https://medical.ev/', {
        method: "POST",
        body: formData
    });

    let veriler = await sonuc;

    return veriler;



    /**
	 * var formData = new FormData();


formData.append('Islem', 'Filtreler');

fetch('https://iyva-dolap.ev', {
  method: 'POST',
  body: formData
})
.then(response => response.json())
.then(result => {
  console.log('Success:', result);
})
.catch(error => {
  console.error('Error:', error);
});
	 */
}

/* toplaGel()
    .then(veriler => veriler.text()).then(function (html) {
		// This is the HTML from our response as a text string
		console.log(html);
	})
	.catch(error => {
	  console.error('Error:', error);
	}); */