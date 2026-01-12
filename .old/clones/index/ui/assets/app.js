// Firebase configuration from your Firebase Console
const firebaseConfig = {
    apiKey: "XXXXXXXXXXXXXXXXXXXXXXXXXX",
    authDomain: "XXXXXXXXXXXX.firebaseapp.com",
    projectId: "XXXXXXXXXXXX",
    storageBucket: "XXXXXXXXXXXX.appspot.com",
    messagingSenderId: "012345678",
    appId: "XXXXXXXXXXXXXXXXXXXXXXXXX"
};
// Initialize Firebase
const app = firebase.initializeApp(firebaseConfig);
const auth = firebase.auth();
// Google Sign-In function
const provider = new firebase.auth.GoogleAuthProvider();
// Sign in with Google when the button is clicked
g.on("click", ".fb_google_login", function () {
    g.ac(g.el("nav"), "loader");
    auth.signInWithPopup(provider)
        .then((result) => {
            // User signed in
            const user = result.user;
            // Get the ID token to send to the backend for verification
            user.getIdToken().then(idToken => {
                var data = {
                    "name": user.displayName,
                    "email": user.email,
                    "emailvf": user.emailVerified,
                    "photo": user.photoURL,
                    "uid": user.uid,
                    "token": idToken
                };
                g.xp("./login", data, function (res) {
                    g.cl(res);
                    window.location.reload(true);
                });
            });
        })
        .catch((error) => {
            g.cl('Error during sign-in:', error);
        });
});

g.on("submit", "form.work_editor", function (form) {
    g.ac(form, "loading");
    var data = JSON.parse(g.xsf(form));
    var url = "./work=add.json";
    if (g.is(form.dataset.hash) && form.dataset.hash !== "") {
        data.hash = form.dataset.hash;
        url = "./work=edit.json";
    }
    g.xp(url, data, function (resp) {
        var rj = JSON.parse(resp);
        g.rc(form, "loading");
        g.ac(form, "dn");
        if (rj.meta.url.state === "success") {
            g.st("refresh", function () { window.location.reload(); }, 1000);
        }
    });
});

g.on("click", "a.nf", function (el) {
    var form = g.el("form.work_editor");
    var editor = g.el("section.editor");
    form.reset();
    delete form.dataset.hash;
    g.ac(editor, "vis");
    g.ac(g.el("a.del"), "dn");
});

g.on("click", "a.edit", function (el) {
    var prnt = el.closest(".g-card");
    var form = g.el("form.work_editor");
    var editor = g.el("section.editor");
    form.dataset.hash = prnt.dataset.hash;
    g.el("[name=title]", form).value = g.el(".g_name", prnt).innerText;
    g.el("[name=summary]", form).value = g.el(".g_summary", prnt).innerText;
    g.el("[name=link]", form).value = g.el(".g_link", prnt).href;
    g.el("[name=image]", form).value = g.el(".g_image", prnt).src;
    g.ac(editor, "vis");
    g.rc(g.el("a.del"), "dn");
});

g.on("click", "a.del", function (el) {
    var form = el.closest("form");
    var hash = form.dataset.hash;
    var url = "./work=delete.json";
    g.xp(url, { "hash": hash }, function (resp) {
        var rj = JSON.parse(resp);
        g.rc(form, "loading");
        g.ac(form, "dn");
        if (rj.meta.url.state === "success") {
            g.st("refresh", function () { window.location.reload(); }, 1000);
        }
    });
});

g.on("click", "a.fav", function (el) {
    var prnt = el.closest(".g-card");
    var hash = prnt.dataset.hash;
    var url = "./work=fav.json";
    g.xp(url, { "hash": hash }, function (resp) {
        var rj = JSON.parse(resp);
        if (rj.meta.url.state === "act") {
            g.ac(el, "act");
        }
        else {
            g.rc(el, "act");
        }
    });
});