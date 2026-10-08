(function () {
    var id = document.currentScript.getAttribute('data-ga4');
    var key = 'genes-consent';

    function read() {
        try { return localStorage.getItem(key); } catch (error) { return null; }
    }

    function save(value) {
        try { localStorage.setItem(key, value); } catch (error) {}
    }

    function loadAnalytics() {
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', id);
        var script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
        document.head.appendChild(script);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var choice = read();
        if (choice === 'yes') return loadAnalytics();
        if (choice === 'no') return;

        var banner = document.getElementById('consent');
        if (!banner) return;
        banner.hidden = false;
        banner.addEventListener('click', function (event) {
            var value = event.target.getAttribute('data-consent');
            if (!value) return;
            save(value);
            banner.hidden = true;
            if (value === 'yes') loadAnalytics();
        });
    });
})();
