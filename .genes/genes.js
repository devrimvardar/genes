/*!
 * ============================================================================
 * Genes Framework JS v2.0
 * ============================================================================
 * A lightweight, powerful JavaScript library for rapid web development
 * 
 * @version     2.0.0
 * @date        2025-10-11
 * @author      Devrim Vardar
 * @copyright   (c) 2024-2025 NodOnce OÜ
 * @license     MIT
 * @link        https://genes.one
 * 
 * FEATURES:
 * - Clean global namespace (window.g)
 * - Function registry and lifecycle hooks
 * - Event delegation system
 * - AJAX helpers (cleaner than fetch)
 * - DOM utilities (shorter than native)
 * - Cookie and localStorage wrappers
 * - Built-in API integration with genes.php
 * - Built-in Auth integration
 * - Token-efficient design
 * 
 * CDN Usage:
 * <script src="https://cdn.genes.one/genes.js"></script>
 * 
 * File Size: ~23KB (unminified)
 * ============================================================================
 */
(function (g) {
    "use strict";

    /* ========================================================================
       1. PRIVATE STATE
       ======================================================================== */

    var app = {
        debug: false,
        env: {},
        cache: {},
        timers: {}
    };

    var fns = {
        evts: {}
    };

    /* ========================================================================
       2. CORE API - State & Function Management
       ======================================================================== */

    /**
     * Set a nested property in app state
     * @param {string} k - Dot-notated key (e.g., "user.name")
     * @param {*} v - Value to set
     */
    g.set = function (k, v) {
        gModifyObject(app, 'set', k, v);
        return v;
    };

    /**
     * Get a nested property from app state
     * @param {string} k - Dot-notated key
     * @return {*} Value
     */
    g.get = function (k) {
        return gModifyObject(app, 'get', k);
    };

    /**
     * Delete a property from app state
     * @param {string} k - Dot-notated key
     */
    g.del = function (k) {
        gModifyObject(app, 'del', k);
    };

    /**
     * Define a function with a key
     * @param {string} k - Function key (can be nested with dots)
     * @param {function} fn - Function to store
     */
    g.def = function (k, fn) {
        gModifyObject(fns, 'set', k, fn);
    };

    /**
     * Return a stored function by key
     * @param {string} k - Function key
     * @return {function} Stored function
     */
    g.ret = function (k) {
        return gModifyObject(fns, 'get', k);
    };

    /**
     * Run a stored function by key with arguments
     * @param {string} k - Function key
     * @param {...*} args - Arguments to pass
     */
    g.run = function (k) {
        var fn = gModifyObject(fns, 'get', k);
        if (g.is(fn)) {
            var args = Array.prototype.slice.call(arguments, 1);
            if (Array.isArray(fn)) {
                for (var i = 0; i < fn.length; i++) {
                    fn[i].apply(null, args);
                }
            } else if (typeof fn === 'function') {
                return fn.apply(null, args);
            }
        }
    };

    /**
     * Queue a function to be called on lifecycle hook
     * @param {string} k - Hook key (e.g., "onInit", "onReady")
     * @param {function} fn - Function to queue
     */
    g.que = function (k, fn) {
        var current = gModifyObject(fns, 'get', k);
        if (!g.is(current)) {
            gModifyObject(fns, 'set', k, []);
            current = [];
        }
        if (!Array.isArray(current)) {
            current = [current];
        }
        current.push(fn);
        gModifyObject(fns, 'set', k, current);
    };

    /* ========================================================================
       3. UTILITY FUNCTIONS
       ======================================================================== */

    /**
     * Check if value is defined and not null/empty
     * @param {*} v - Value to check
     * @return {boolean}
     */
    g.is = function (v) {
        return !(
            typeof v === "undefined" ||
            v === null ||
            v === "null" ||
            v === "" ||
            v === false
        );
    };

    /**
     * Check if value is a function
     * @param {*} v - Value to check
     * @return {boolean}
     */
    g.is_fnc = function (v) {
        return typeof v === 'function';
    };

    /**
     * Check if value is an object (not array, not null)
     * @param {*} v - Value to check
     * @return {boolean}
     */
    g.is_obj = function (v) {
        return (typeof v === "object" && !Array.isArray(v) && v !== null);
    };

    /**
     * Console log with timestamp (only if debug mode)
     * @param {*} msg - Message to log
     */
    g.cl = function (msg) {
        if (app.debug || (typeof console !== 'undefined' && console.log)) {
            var timestamp = g.now();
            console.log('[' + timestamp + ']', msg);
        }
    };

    /**
     * Get formatted date/time
     * @param {number} ms - Milliseconds timestamp (optional)
     * @param {string} format - Format string (Y-m-d H:i:s)
     * @return {string} Formatted date
     */
    g.now = function (ms, format) {
        var d = g.is(ms) ? new Date(parseFloat(ms)) : new Date();
        var f = g.is(format) ? format : "Y-m-d H:i:s";
        return f
            .replace(/Y/g, d.getFullYear())
            .replace(/m/g, ('0' + (d.getMonth() + 1)).slice(-2))
            .replace(/d/g, ('0' + d.getDate()).slice(-2))
            .replace(/H/g, ('0' + d.getHours()).slice(-2))
            .replace(/i/g, ('0' + d.getMinutes()).slice(-2))
            .replace(/s/g, ('0' + d.getSeconds()).slice(-2));
    };

    /**
     * Base64 encode string
     * @param {string} str - String to encode
     * @return {string} Encoded string
     */
    g.encode = function (str) {
        try {
            return btoa(encodeURIComponent(str));
        } catch (e) {
            return str;
        }
    };

    /**
     * Base64 decode string
     * @param {string} str - String to decode
     * @return {string} Decoded string
     */
    g.decode = function (str) {
        try {
            return decodeURIComponent(atob(str));
        } catch (e) {
            return str;
        }
    };

    /**
     * Debounce function execution
     * @param {function} fn - Function to debounce
     * @param {number} ms - Milliseconds to wait
     * @return {function} Debounced function
     */
    g.debounce = function (fn, ms) {
        var timer;
        return function () {
            var context = this;
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(context, args);
            }, ms);
        };
    };

    /**
     * Throttle function execution
     * @param {function} fn - Function to throttle
     * @param {number} ms - Milliseconds between calls
     * @return {function} Throttled function
     */
    g.throttle = function (fn, ms) {
        var inThrottle;
        return function () {
            var context = this;
            var args = arguments;
            if (!inThrottle) {
                fn.apply(context, args);
                inThrottle = true;
                setTimeout(function () {
                    inThrottle = false;
                }, ms);
            }
        };
    };

    /* ========================================================================
       4. TIMER FUNCTIONS
       ======================================================================== */

    /**
     * Set interval with key
     * @param {string} k - Timer key
     * @param {function} fn - Function to execute
     * @param {number} ms - Milliseconds
     */
    g.si = function (k, fn, ms) {
        app.timers[k] = setInterval(fn, ms);
    };

    /**
     * Set timeout with key
     * @param {string} k - Timer key
     * @param {function} fn - Function to execute
     * @param {number} ms - Milliseconds
     */
    g.st = function (k, fn, ms) {
        app.timers[k] = setTimeout(fn, ms);
    };

    /**
     * Clear interval by key
     * @param {string} k - Timer key
     */
    g.ci = function (k) {
        if (g.is(app.timers[k])) {
            clearInterval(app.timers[k]);
            delete app.timers[k];
        }
    };

    /**
     * Clear timeout by key
     * @param {string} k - Timer key
     */
    g.ct = function (k) {
        if (g.is(app.timers[k])) {
            clearTimeout(app.timers[k]);
            delete app.timers[k];
        }
    };

    /* ========================================================================
       5. DOM UTILITIES
       ======================================================================== */

    /**
     * Query selector (returns first match)
     * @param {string} selector - CSS selector
     * @param {Element} parent - Parent element (optional)
     * @return {Element} Element
     */
    g.el = function (selector, parent) {
        parent = parent || document;
        return parent.querySelector(selector);
    };

    /**
     * Query selector all (returns NodeList)
     * @param {string} selector - CSS selector
     * @param {Element} parent - Parent element (optional)
     * @return {NodeList} Elements
     */
    g.els = function (selector, parent) {
        parent = parent || document;
        return parent.querySelectorAll(selector);
    };

    /**
     * Create element with attributes
     * @param {string} tag - Tag name
     * @param {object} attrs - Attributes object (optional)
     * @return {Element} Created element
     */
    g.create = function (tag, attrs) {
        var el = document.createElement(tag);
        if (g.is_obj(attrs)) {
            for (var key in attrs) {
                if (key === 'className') {
                    el.className = attrs[key];
                } else if (key === 'innerHTML') {
                    el.innerHTML = attrs[key];
                } else if (key === 'textContent') {
                    el.textContent = attrs[key];
                } else {
                    el.setAttribute(key, attrs[key]);
                }
            }
        }
        return el;
    };

    /**
     * Check if element has class
     * @param {Element} el - Element
     * @param {string} cls - Class name
     * @return {boolean}
     */
    g.hc = function (el, cls) {
        return el.classList.contains(cls);
    };

    /**
     * Add class to element
     * @param {Element} el - Element
     * @param {string} cls - Class name
     */
    g.ac = function (el, cls) {
        el.classList.add(cls);
    };

    /**
     * Remove class from element
     * @param {Element} el - Element
     * @param {string} cls - Class name
     */
    g.rc = function (el, cls) {
        el.classList.remove(cls);
    };

    /**
     * Toggle class on element
     * @param {Element} el - Element
     * @param {string} cls - Class name
     */
    g.tc = function (el, cls) {
        el.classList.toggle(cls);
    };

    /**
     * Check if element is visible
     * @param {Element} el - Element
     * @return {boolean}
     */
    g.vis = function (el) {
        if (!g.is(el)) return false;
        var style = window.getComputedStyle(el);
        return style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0';
    };

    /**
     * Get element index in parent
     * @param {Element} el - Element
     * @return {number} Index
     */
    g.ix = function (el) {
        return Array.prototype.indexOf.call(el.parentNode.children, el);
    };

    /**
     * Trigger click on element
     * @param {Element|string} target - Element or selector
     */
    g.click = function (target) {
        var el = typeof target === 'string' ? g.el(target) : target;
        if (g.is(el)) {
            el.click();
        }
    };

    /**
     * Toggle element visibility (display none)
     * @param {Element|string} target - Element or selector
     */
    g.toggle = function (target) {
        var el = typeof target === 'string' ? g.el(target) : target;
        if (g.is(el)) {
            g.tc(el, 'hidden');
        }
    };

    /* ========================================================================
       6. EVENT SYSTEM
       ======================================================================== */

    /**
     * Delegated event listener
     * @param {string} event - Event name (click, submit, etc.)
     * @param {string} selector - CSS selector
     * @param {function} callback - Callback function
     */
    g.on = function (event, selector, callback) {
        var encoded = g.encode(selector);
        if (!g.is(fns.evts[event])) {
            fns.evts[event] = {};
            gListenEvent(event);
        }
        fns.evts[event][encoded] = {
            selector: selector,
            callback: callback
        };
    };

    /**
     * Remove delegated event listener
     * @param {string} event - Event name
     * @param {string} selector - CSS selector
     */
    g.off = function (event, selector) {
        var encoded = g.encode(selector);
        if (g.is(fns.evts[event]) && g.is(fns.evts[event][encoded])) {
            delete fns.evts[event][encoded];
        }
    };

    /**
     * One-time delegated event
     * @param {string} event - Event name
     * @param {string} selector - CSS selector
     * @param {function} callback - Callback function
     */
    g.once = function (event, selector, callback) {
        g.on(event, selector, function (el) {
            callback(el);
            g.off(event, selector);
        });
    };

    /**
     * Trigger custom event on element
     * @param {Element} el - Element
     * @param {string} event - Event name
     * @param {object} detail - Event detail data (optional)
     */
    g.trigger = function (el, event, detail) {
        var evt = new CustomEvent(event, {
            bubbles: true,
            cancelable: true,
            detail: detail || {}
        });
        el.dispatchEvent(evt);
    };

    /* ========================================================================
       6.5 DATA BINDING SYSTEM
       ======================================================================== */

    /**
     * Bind data to element with data-g-* attributes
     * @param {Element} el - Root element to bind
     * @param {object} data - Data object
     * 
     * Supports:
     * - data-g-bind="key" - Sets textContent
     * - data-g-attr="attr:key" - Sets attribute (e.g., "src:image.url")
     * - data-g-if="key" - Shows/hides element based on truthy value
     * - data-g-for="item in array" - Repeats element for each item
     * - data-g-class="className:condition" - Toggles class based on condition
     * 
     * @example
     * HTML: <div data-g-bind="title"></div>
     * JS: g.bind(element, {title: 'Hello World'})
     */
    g.bind = function (el, data) {
        if (!el || !data) return;

        // Handle data-g-bind (text content)
        var bindEls = el.querySelectorAll('[data-g-bind]');
        for (var i = 0; i < bindEls.length; i++) {
            var bindEl = bindEls[i];
            var key = bindEl.getAttribute('data-g-bind');
            var value = gGetNestedValue(data, key);
            if (g.is(value)) {
                bindEl.textContent = value;
            }
        }

        // Handle data-g-attr (attributes)
        var attrEls = el.querySelectorAll('[data-g-attr]');
        for (var j = 0; j < attrEls.length; j++) {
            var attrEl = attrEls[j];
            var attrStr = attrEl.getAttribute('data-g-attr');
            var pairs = attrStr.split(',');
            
            for (var k = 0; k < pairs.length; k++) {
                var pair = pairs[k].trim().split(':');
                if (pair.length === 2) {
                    var attr = pair[0].trim();
                    var dataKey = pair[1].trim();
                    var attrValue = gGetNestedValue(data, dataKey);
                    if (g.is(attrValue)) {
                        attrEl.setAttribute(attr, attrValue);
                    }
                }
            }
        }

        // Handle data-g-if (conditional rendering)
        var ifEls = el.querySelectorAll('[data-g-if]');
        for (var l = 0; l < ifEls.length; l++) {
            var ifEl = ifEls[l];
            var condition = ifEl.getAttribute('data-g-if');
            var condValue = gGetNestedValue(data, condition);
            
            if (condValue) {
                ifEl.style.display = '';
            } else {
                ifEl.style.display = 'none';
            }
        }

        // Handle data-g-class (conditional classes)
        var classEls = el.querySelectorAll('[data-g-class]');
        for (var m = 0; m < classEls.length; m++) {
            var classEl = classEls[m];
            var classStr = classEl.getAttribute('data-g-class');
            var classPairs = classStr.split(',');
            
            for (var n = 0; n < classPairs.length; n++) {
                var classPair = classPairs[n].trim().split(':');
                if (classPair.length === 2) {
                    var className = classPair[0].trim();
                    var classCondition = classPair[1].trim();
                    var classValue = gGetNestedValue(data, classCondition);
                    
                    if (classValue) {
                        g.ac(classEl, className);
                    } else {
                        g.rc(classEl, className);
                    }
                }
            }
        }
    };

    /**
     * Get nested value from object using dot notation
     * @private
     */
    function gGetNestedValue(obj, key) {
        if (!key) return obj;
        var keys = key.split('.');
        var value = obj;
        
        for (var i = 0; i < keys.length; i++) {
            if (value && typeof value === 'object') {
                value = value[keys[i]];
            } else {
                return undefined;
            }
        }
        
        return value;
    }

    /* ========================================================================
       7. AJAX FUNCTIONS
       ======================================================================== */

    /**
     * AJAX GET request
     * @param {string} url - URL
     * @param {function} success - Success callback
     * @param {function} error - Error callback (optional)
     * @return {XMLHttpRequest}
     */
    g.get = function (url, success, error) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    if (g.is_fnc(success)) {
                        success(xhr.responseText, xhr);
                    }
                } else {
                    if (g.is_fnc(error)) {
                        error(xhr);
                    }
                }
            }
        };
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send();
        return xhr;
    };

    /**
     * AJAX POST request
     * @param {string} url - URL
     * @param {object} data - Data object
     * @param {function} success - Success callback
     * @param {function} error - Error callback (optional)
     * @return {XMLHttpRequest}
     */
    g.post = function (url, data, success, error) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    if (g.is_fnc(success)) {
                        success(xhr.responseText, xhr);
                    }
                } else {
                    if (g.is_fnc(error)) {
                        error(xhr);
                    }
                }
            }
        };
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        var params = gSerializeObject(data);
        xhr.send(params);
        return xhr;
    };

    /**
     * AJAX POST JSON request
     * @param {string} url - URL
     * @param {object} data - Data object
     * @param {function} success - Success callback
     * @param {function} error - Error callback (optional)
     * @return {XMLHttpRequest}
     */
    g.json = function (url, data, success, error) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    if (g.is_fnc(success)) {
                        success(xhr.responseText, xhr);
                    }
                } else {
                    if (g.is_fnc(error)) {
                        error(xhr);
                    }
                }
            }
        };
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify(data));
        return xhr;
    };

    /**
     * Serialize form to JSON string
     * @param {Element} form - Form element
     * @return {string} JSON string
     */
    g.form = function (form) {
        var obj = {};
        var elements = g.els('input, select, textarea', form);

        for (var i = 0; i < elements.length; i++) {
            var el = elements[i];
            var name = el.name;
            var value = el.value;

            if (!g.is(name) || el.disabled) {
                continue;
            }

            if (el.type === 'radio') {
                if (el.checked) {
                    obj[name] = value;
                }
            } else if (el.type === 'checkbox') {
                if (el.checked) {
                    if (!g.is(obj[name])) {
                        obj[name] = [];
                    }
                    if (Array.isArray(obj[name])) {
                        obj[name].push(value);
                    } else {
                        obj[name] = value;
                    }
                }
            } else {
                obj[name] = value;
            }
        }

        return JSON.stringify(obj);
    };

    /**
     * Upload file via AJAX
     * @param {string} url - URL
     * @param {FormData} formData - FormData object
     * @param {function} success - Success callback
     * @param {function} error - Error callback (optional)
     * @param {function} progress - Progress callback (optional)
     * @return {XMLHttpRequest}
     */
    g.upload = function (url, formData, success, error, progress) {
        var xhr = new XMLHttpRequest();

        if (g.is_fnc(progress)) {
            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    var percent = (e.loaded / e.total) * 100;
                    progress(percent, e);
                }
            });
        }

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    if (g.is_fnc(success)) {
                        success(xhr.responseText, xhr);
                    }
                } else {
                    if (g.is_fnc(error)) {
                        error(xhr);
                    }
                }
            }
        };

        xhr.open('POST', url);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
        return xhr;
    };

    /**
     * Abort AJAX request
     * @param {XMLHttpRequest} xhr - XMLHttpRequest object
     */
    g.abort = function (xhr) {
        if (xhr && xhr.readyState !== 4) {
            xhr.abort();
        }
    };

    /* ========================================================================
       8. STORAGE FUNCTIONS
       ======================================================================== */

    // Cookie namespace
    g.cookie = {};

    /**
     * Set cookie (stores objects as JSON)
     * @param {string} name - Cookie name
     * @param {*} value - Value (will be JSON stringified if object)
     * @param {number} days - Expiration days (default 30)
     */
    g.cookie.set = function (name, value, days) {
        days = days || 30;
        var val = g.is_obj(value) ? JSON.stringify(value) : value;
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + val + '; expires=' + date.toUTCString() + '; path=/';
    };

    /**
     * Get cookie (auto-parses JSON)
     * @param {string} name - Cookie name
     * @return {*} Value
     */
    g.cookie.get = function (name) {
        var nameEQ = name + '=';
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) {
                var val = c.substring(nameEQ.length, c.length);
                try {
                    return JSON.parse(val);
                } catch (e) {
                    return val;
                }
            }
        }
        return null;
    };

    /**
     * Delete cookie
     * @param {string} name - Cookie name
     */
    g.cookie.del = function (name) {
        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
    };

    // LocalStorage namespace
    g.ls = {};

    /**
     * Set localStorage item (auto-stringifies objects)
     * @param {string} key - Key
     * @param {*} value - Value
     */
    g.ls.set = function (key, value) {
        try {
            var val = g.is_obj(value) || Array.isArray(value) ? JSON.stringify(value) : value;
            localStorage.setItem(key, val);
        } catch (e) {
            g.cl('localStorage.setItem error: ' + e);
        }
    };

    /**
     * Get localStorage item (auto-parses JSON)
     * @param {string} key - Key
     * @return {*} Value
     */
    g.ls.get = function (key) {
        try {
            var val = localStorage.getItem(key);
            if (!g.is(val)) return null;
            try {
                return JSON.parse(val);
            } catch (e) {
                return val;
            }
        } catch (e) {
            g.cl('localStorage.getItem error: ' + e);
            return null;
        }
    };

    /**
     * Delete localStorage item
     * @param {string} key - Key
     */
    g.ls.del = function (key) {
        try {
            localStorage.removeItem(key);
        } catch (e) {
            g.cl('localStorage.removeItem error: ' + e);
        }
    };

    /**
     * Clear all localStorage
     */
    g.ls.clear = function () {
        try {
            localStorage.clear();
        } catch (e) {
            g.cl('localStorage.clear error: ' + e);
        }
    };

    /* ========================================================================
       9. URL UTILITIES
       ======================================================================== */

    g.url = {};

    /**
     * Get all URL parameters as object
     * @return {object} Parameters object
     */
    g.url.params = function () {
        var params = {};
        var search = window.location.search.substring(1);
        if (!g.is(search)) return params;

        var pairs = search.split('&');
        for (var i = 0; i < pairs.length; i++) {
            var pair = pairs[i].split('=');
            var key = decodeURIComponent(pair[0]);
            var value = decodeURIComponent(pair[1] || '');
            params[key] = value;
        }
        return params;
    };

    /**
     * Get single URL parameter
     * @param {string} key - Parameter key
     * @return {string} Parameter value
     */
    g.url.param = function (key) {
        var params = g.url.params();
        return params[key] || null;
    };

    /**
     * Update URL without reloading page
     * @param {string} url - New URL
     * @param {object} state - State object (optional)
     */
    g.url.push = function (url, state) {
        if (window.history && window.history.pushState) {
            window.history.pushState(state || {}, '', url);
        }
    };

    /**
     * Replace URL without reloading page
     * @param {string} url - New URL
     * @param {object} state - State object (optional)
     */
    g.url.replace = function (url, state) {
        if (window.history && window.history.replaceState) {
            window.history.replaceState(state || {}, '', url);
        }
    };

    /* ========================================================================
       10. API INTEGRATION (genes.php)
       ======================================================================== */

    g.api = {
        base: './api' // RESTful API base path
    };

    /**
     * Configure API base path
     * @param {string} path - Base path (e.g., '/api' or '/v1/api')
     */
    g.api.config = function (path) {
        g.api.base = path;
    };

    /**
     * List records from table
     * @param {string} table - Table name
     * @param {object} params - Query parameters (optional)
     * @param {function} success - Success callback
     * @param {function} error - Error callback (optional)
     * 
     * @example g.api.list('clones', {page: 1, limit: 20}, function(data) { ... });
     */
    g.api.list = function (table, params, success, error) {
        // Handle optional params
        if (g.is_fnc(params)) {
            error = success;
            success = params;
            params = {};
        }
        params = params || {};

        // Build URL with query params
        var url = g.api.base + '/' + table;
        var queryString = gSerializeObject(params);
        if (queryString) {
            url += '?' + queryString;
        }

        g.get(url, function (response) {
            try {
                var data = JSON.parse(response);
                if (g.is_fnc(success)) success(data);
            } catch (e) {
                if (g.is_fnc(error)) error(e);
            }
        }, error);
    };

    /**
     * Get single record
     * @param {string} table - Table name
     * @param {string} hash - Record hash
     * @param {function} success - Success callback
     * @param {function} error - Error callback (optional)
     * 
     * @example g.api.get('clones', 'abc123', function(data) { ... });
     */
    g.api.get = function (table, hash, success, error) {
        var url = g.api.base + '/' + table + '/' + hash;

        g.get(url, function (response) {
            try {
                var data = JSON.parse(response);
                if (g.is_fnc(success)) success(data);
            } catch (e) {
                if (g.is_fnc(error)) error(e);
            }
        }, error);
    };

    /**
     * Create new record
     * @param {string} table - Table name
     * @param {object} data - Record data
     * @param {function} success - Success callback
     * @param {function} error - Error callback (optional)
     * 
     * @example g.api.create('clones', {title: 'New Clone'}, function(result) { ... });
     */
    g.api.create = function (table, data, success, error) {
        var url = g.api.base + '/' + table;

        g.json(url, data, function (response) {
            try {
                var result = JSON.parse(response);
                if (g.is_fnc(success)) success(result);
            } catch (e) {
                if (g.is_fnc(error)) error(e);
            }
        }, error);
    };

    /**
     * Update record
     * @param {string} table - Table name
     * @param {string} hash - Record hash
     * @param {object} data - Updated data
     * @param {function} success - Success callback
     * @param {function} error - Error callback (optional)
     * 
     * @example g.api.update('clones', 'abc123', {title: 'Updated'}, function(result) { ... });
     */
    g.api.update = function (table, hash, data, success, error) {
        var url = g.api.base + '/' + table + '/' + hash;

        g.json(url, data, function (response) {
            try {
                var result = JSON.parse(response);
                if (g.is_fnc(success)) success(result);
            } catch (e) {
                if (g.is_fnc(error)) error(e);
            }
        }, error);
    };

    /**
     * Delete record
     * @param {string} table - Table name
     * @param {string} hash - Record hash
     * @param {function} success - Success callback
     * @param {function} error - Error callback (optional)
     * 
     * @example g.api.delete('clones', 'abc123', function(result) { ... });
     */
    g.api.delete = function (table, hash, success, error) {
        var url = g.api.base + '/' + table + '/' + hash;

        // Create a DELETE request using XMLHttpRequest
        var xhr = new XMLHttpRequest();
        xhr.open('DELETE', url);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        var result = JSON.parse(xhr.responseText);
                        if (g.is_fnc(success)) success(result);
                    } catch (e) {
                        if (g.is_fnc(error)) error(e);
                    }
                } else {
                    if (g.is_fnc(error)) error(xhr);
                }
            }
        };
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send();
        return xhr;
    };

    /* ========================================================================
       11. AUTH INTEGRATION (genes.php)
       ======================================================================== */

    g.auth = {};

    /**
     * Login user
     * @param {string} username - Username
     * @param {string} password - Password
     * @param {function} success - Success callback
     * @param {function} error - Error callback (optional)
     */
    g.auth.login = function (username, password, success, error) {
        g.post(g.api.endpoint + '?action=login', {
            username: username,
            password: password
        }, function (response) {
            try {
                var result = JSON.parse(response);
                if (g.is_fnc(success)) success(result);
            } catch (e) {
                if (g.is_fnc(error)) error(e);
            }
        }, error);
    };

    /**
     * Logout user
     * @param {function} success - Success callback
     * @param {function} error - Error callback (optional)
     */
    g.auth.logout = function (success, error) {
        g.post(g.api.endpoint + '?action=logout', {}, function (response) {
            try {
                var result = JSON.parse(response);
                if (g.is_fnc(success)) success(result);
            } catch (e) {
                if (g.is_fnc(error)) error(e);
            }
        }, error);
    };

    /**
     * Check authentication status
     * @param {function} success - Success callback
     * @param {function} error - Error callback (optional)
     */
    g.auth.check = function (success, error) {
        g.get(g.api.endpoint + '?action=check', function (response) {
            try {
                var result = JSON.parse(response);
                if (g.is_fnc(success)) success(result);
            } catch (e) {
                if (g.is_fnc(error)) error(e);
            }
        }, error);
    };

    /* ========================================================================
       12. PRIVATE HELPER FUNCTIONS
       ======================================================================== */

    /**
     * Modify nested object property
     * @private
     */
    function gModifyObject(obj, mode, key, value) {
        if (!g.is(key)) return obj;

        var keys = typeof key === 'string' ? key.split('.') : [key];
        var current = obj;

        for (var i = 0; i < keys.length - 1; i++) {
            if (!current[keys[i]]) {
                current[keys[i]] = {};
            }
            current = current[keys[i]];
        }

        var lastKey = keys[keys.length - 1];

        if (mode === 'set') {
            current[lastKey] = value;
            return value;
        } else if (mode === 'get') {
            return current[lastKey];
        } else if (mode === 'del') {
            delete current[lastKey];
        }
    }

    /**
     * Serialize object to URL params
     * @private
     */
    function gSerializeObject(obj) {
        var params = [];
        for (var key in obj) {
            if (obj.hasOwnProperty(key)) {
                params.push(encodeURIComponent(key) + '=' + encodeURIComponent(obj[key]));
            }
        }
        return params.join('&');
    }

    /**
     * Setup event listener for delegation
     * @private
     */
    function gListenEvent(event) {
        document.addEventListener(event, function (e) {
            if (!fns.evts[event]) return;

            for (var encoded in fns.evts[event]) {
                var handler = fns.evts[event][encoded];
                var target = e.target.closest(handler.selector);

                if (target) {
                    e.preventDefault();
                    handler.callback(target, e);
                }
            }
        });
    }

    /* ========================================================================
       13. INITIALIZATION
       ======================================================================== */

    // Run queued init functions
    g.que('onInit', function () {
        g.cl('Genes.js v2.0 initialized');
    });

    // Auto-run onInit when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            g.run('onInit');
        });
    } else {
        g.run('onInit');
    }

    // Run queued load functions
    window.addEventListener('load', function () {
        g.run('onLoad');
    });

    /* ========================================================================
       END OF GENES.JS
       ======================================================================== */

})((window.g = {}));

document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to the clicked button and corresponding content
            button.classList.add('active');
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
});
