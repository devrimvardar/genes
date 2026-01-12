// Genes Website JavaScript
// Uses genes.js event delegation system

// Tab switching using g.on() for event delegation
g.on('click', '.tab-btn', function (el) {
    var targetTab = el.getAttribute('data-tab');
    
    // Remove active class from all buttons and panes
    var tabButtons = g.els('.tab-btn');
    var tabPanes = g.els('.tab-pane');
    
    for (var i = 0; i < tabButtons.length; i++) {
        g.rc(tabButtons[i], 'active');
    }
    for (var i = 0; i < tabPanes.length; i++) {
        g.rc(tabPanes[i], 'active');
    }
    
    // Add active class to clicked button and corresponding pane
    g.ac(el, 'active');
    var targetPane = g.el('#' + targetTab);
    if (targetPane) {
        g.ac(targetPane, 'active');
    }
});
