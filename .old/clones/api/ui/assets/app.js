// get ping to detect login status, show hide accordingly
g.que("atLoad.todo", function () {
    g.cl("Todo loaded.");
    g.xg("./ping.json", function (resp) {
        var rj = JSON.parse(resp);
        var ut = rj.meta.user.type;
        if (ut !== "guest") {
            g.els(".guest").forEach(el => { g.ac(el, "dn"); });
            g.els(".user").forEach(el => { g.rc(el, "dn"); });
            todo_list_tasks(rj.meta.base);
            g.el("em.alias").innerHTML = rj.meta.user.alias;
        }
        else {
            g.els(".user").forEach(el => { g.ac(el, "dn"); });
            g.els(".guest:not(.tog)").forEach(el => { g.rc(el, "dn"); });
        }
    });
});

// form register.
g.on("submit", "form.register", function (form) {
    var u = g.el("input.u", form).value;
    var p = g.el("input.p", form).value;
    g.cl(u + "|" + p);
    // check if login exists, if not register and login, if yes show msg
    g.xp("./todo-register.json", { "username": u, "password": p }, function (resp) {
        var rj = JSON.parse(resp);
        g.msgs(rj);
        if (g.is(rj.meta.redirect)) { g.run("atLoad.todo"); }
    });
});

// form login.
g.on("submit", "form.login", function (form) {
    var u = g.el("input.u", form).value;
    var p = g.el("input.p", form).value;
    // check if login exists, if yes render users, if not show msg
    g.xp("./todo-login.json", { "username": u, "password": p }, function (resp) {
        var rj = JSON.parse(resp);
        g.msgs(rj);
        if (g.is(rj.meta.redirect)) { g.run("atLoad.todo"); }
    });
});

// todo logout.
g.on("click", "a.logout", function (el) {
    // delete session
    g.xg("./todo-logout.json", function (resp) {
        var rj = JSON.parse(resp);
        g.msgs(rj);
        if (g.is(rj.meta.redirect)) { g.run("atLoad.todo"); }
    });
});

// list tasks
function todo_list_tasks(rows) {
    var tbl = g.el(".user table tbody");
    var iht = "";
    var i = 1;
    rows.forEach(row => {
        var chk = (g.is(row.ok) && row.ok == "y") ? "checked" : "";
        iht += '<tr data-id="' + i + '" class="' + chk + '"><td>' + row.ty + '</td><td>' + row.ta + '</td><td class="c">' +
            '<input type="checkbox" class="cb" value="y" id="i' + i + '" ' + chk + '><label for="i' + i + '" class="p0"></label>'
            + '</td><td class="a">'
            + '<a href="#" class="e"><i class="icon_pencil"></i></a>'
            + '<div class="dib g-drop pr">'
            + '<a class="open g-tocss" data-g="to:closesttr;cs:active" href="#"><i class="icon_trash"></i></a>'
            + '<div class="menu dn w24r"><div class="alert type-3 tar p2r">'
            + '<h5 class="at">Are you sure you want to delete this?</h5>'
            + '<a href="#" class="button d w6r">Yes</a>'
            + '<a href="#" class="button link g-hide g-tocss" data-g="hd:.menu;to:closesttr;cs:active">No</a>'
            + '</div></div>'
            + '</div>'
            + '</td></tr>';
        i++;
    });
    tbl.innerHTML = iht;
}

// add task
g.on("submit", "form.task", function (form) {
    var ty = g.el("input.ty", form).value;
    var ta = g.el("input.ta", form).value;
    var url = "./todo-add.json";
    var data = { "ty": ty, "ta": ta };
    if (g.is(form.dataset.id)) {
        url = "./todo-edit.json";
        data = { "ty": ty, "ta": ta, "id": form.dataset.id }
    }
    g.xp(url, data, function (resp) {
        var rj = JSON.parse(resp);
        g.run("atLoad.todo");
    });
});

// edit task
g.on("click", "a.e", function (el) {
    var tr = el.closest("tr");
    var table = tr.closest("table");
    var siblings = g.els("tr", table);
    for (var i = 0; i < siblings.length; i++) {
        var row = siblings[i];
        g.rc(row, "active");
    }
    g.ac(tr, "active");
    g.el("input.ty").value = g.els("td", tr)[0].innerHTML;
    g.el("input.ta").value = g.els("td", tr)[1].innerHTML;
    g.el("input.s").value = g.el("b.s").innerHTML = "Edit";
    g.el("form.task").dataset.id = tr.dataset.id;
});

// reset form
g.on("click", "button.r", function (el) {
    g.el("input.s").value = g.el("b.s").innerHTML = "Add";
    g.el("input.ty").value = "";
    g.el("input.ta").value = "";
    var act_row = g.el(".list table tr.active");
    if (g.is(act_row)) { g.rc(act_row, "active"); }
    g.el("form.task").removeAttribute("data-id");
});

// delete task
g.on("click", "a.d", function (el) {
    var id = el.closest("tr.active").dataset.id;
    g.xp("./todo-del.json", { "id": id }, function (resp) {
        var rj = JSON.parse(resp);
        g.run("atLoad.todo");
    });
});

g.on("change", ".cb", function (el) {
    var id = el.id.replace("i", "");
    var ok = (el.checked) ? "y" : "n";
    if (g.is(id)) {
        url = "./todo-edit.json";
        data = { "ok": ok, "id": id }
    }
    g.xp(url, data, function (resp) {
        var rj = JSON.parse(resp);
        g.run("atLoad.todo");
    });
});