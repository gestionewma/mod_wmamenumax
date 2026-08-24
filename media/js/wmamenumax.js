/**
 * @package     Wma.Module.WmaMenumax
 * @subpackage  mod_wmamenumax
 *
* @author      Team Developer by WMA Web Maker Agency <wmaextension@gmail.com>
 * @copyright   (C) 2026 WMA Web Maker Agency. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @version     1.0.11
 * @date        20/08/2026
 * @file        media/js/wmamenumax.js
 */
(function () {
    'use strict';

    var TAP_TIMEOUT = 400;

    function init(nav) {
        var pct     = parseInt(nav.getAttribute('data-panel-pct') || '100', 10) || 100;
        var isTouch = ('ontouchstart' in window) || (navigator.maxTouchPoints && navigator.maxTouchPoints > 0);
        var toggle  = nav.querySelector('.wma-mm-toggle');
        var bar     = nav.querySelector('.wma-mm-bar');

        if (!bar) {
            return;
        }

        // Panel image swap on child hover (mouse)
        nav.querySelectorAll('.wma-mm-item.has-mega').forEach(function (item) {
            var mega     = item.querySelector('.wma-mm-mega');
            var panelImg = item.querySelector('.wma-mm-panel-img');

            if (!mega) {
                return;
            }

            item.addEventListener('mouseenter', function () {
                sizePanel(item, pct);
            });

            if (panelImg) {
                item.querySelectorAll('.wma-mm-child[data-hover]').forEach(function (child) {
                    child.addEventListener('mouseenter', function () {
                        panelImg.src = child.getAttribute('data-hover');
                    });

                    child.addEventListener('mouseleave', function () {
                        panelImg.src = panelImg.getAttribute('data-default');
                    });
                });
            }
        });

        // Mobile toggle button
        if (toggle) {
toggle.addEventListener('click', function () {
                var expanded = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                nav.classList.toggle('is-mobile-open', !expanded);
            });
        }

        // Accordion toggle: tapping a top level item with children expands/collapses
        bar.querySelectorAll('.wma-mm-item.has-mega > .wma-mm-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                var item = link.parentElement;

                // Desktop: rely on CSS hover, let the link behave normally
                if (!window.matchMedia('(max-width: 992px)').matches) {
                    return;
                }

                e.preventDefault();

                var wasOpen = item.classList.contains('is-open');

                closeAllItems(bar);

                if (!wasOpen) {
                    item.classList.add('is-open');
                    sizePanel(item, pct);
                }
            });
        });

        // Touch: 1 tap = preview, 2 tap = navigate
        if (isTouch) {
            bar.querySelectorAll('.wma-mm-child[data-hover]').forEach(function (child) {
                var lastTap = 0;
                var timer   = null;

                child.addEventListener('click', function (e) {
                    var now = Date.now();

                    if (now - lastTap < TAP_TIMEOUT) {
                        lastTap = 0;
                        clearTimeout(timer);
                        return;
                    }

                    lastTap = now;
                    e.preventDefault();

                    var item     = child.closest('.wma-mm-item');
                    var panelImg = item ? item.querySelector('.wma-mm-panel-img') : null;

                    if (panelImg && child.hasAttribute('data-hover')) {
                        panelImg.src = child.getAttribute('data-hover');
                    }

                    if (item) {
                        item.classList.add('is-open');
                        sizePanel(item, pct);
                    }

                    timer = setTimeout(function () {
                        lastTap = 0;
                    }, TAP_TIMEOUT);
                });
            });
        }
    }

    function closeAllItems(bar) {
        bar.querySelectorAll('.wma-mm-item.has-mega.is-open').forEach(function (item) {
            item.classList.remove('is-open');
        });
    }

    function sizePanel(item, pct) {
        var mega  = item.querySelector('.wma-mm-mega');
        var panel = item.querySelector('.wma-mm-panel');

        if (!mega || !panel) {
            return;
        }

        var tallest = 0;

        mega.querySelectorAll('.wma-mm-col').forEach(function (col) {
            var h = col.offsetHeight;
            if (h > tallest) {
                tallest = h;
            }
        });

        if (tallest > 0) {
            var size = Math.round(tallest * (pct / 100));
            panel.style.setProperty('--wma-panel-size', size + 'px');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.wma-mm').forEach(init);
    });
})();
