(function () {
    var id = document.currentScript.getAttribute('data-ga4');
    var key = 'genes-consent';

    // Time zones where analytics needs consent first: EU/EEA, UK, Switzerland, and
    // Türkiye (KVKK). Every Europe/* zone counts; these are the others.
    var consentZones = [
        'Asia/Nicosia', 'Asia/Famagusta', 'Asia/Istanbul', 'Atlantic/Reykjavik', 'Atlantic/Canary',
        'Atlantic/Madeira', 'Atlantic/Azores', 'Atlantic/Faroe', 'Arctic/Longyearbyen'
    ];

    function read() {
        try { return localStorage.getItem(key); } catch (error) { return null; }
    }

    function save(value) {
        try { localStorage.setItem(key, value); } catch (error) {}
    }

    function timeZone() {
        try { return Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (error) { return ''; }
    }

    // Unknown time zones ask for consent, so the safe answer is the default.
    function needsConsent(zone) {
        return zone === '' || zone.indexOf('Europe/') === 0 || consentZones.indexOf(zone) !== -1;
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
        if (navigator.globalPrivacyControl === true) return;
        var choice = read();
        if (choice === 'yes') return loadAnalytics();
        if (choice === 'no') return;
        if (!needsConsent(timeZone())) return loadAnalytics();

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
