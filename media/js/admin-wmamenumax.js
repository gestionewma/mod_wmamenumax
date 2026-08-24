/**
 * @package     Wma.Module.WmaMenumax
 * @subpackage  mod_wmamenumax
 *
* @author      Team Developer by WMA Web Maker Agency <wmaextension@gmail.com>
 * @copyright   (C) 2026 WMA Web Maker Agency. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @version     1.0.11
 * @date        20/08/2026
 * @file        media/js/admin-wmamenumax.js
 */
(function () {
    'use strict';

    function init() {
        var container = document.querySelector('.wma-mm-maintenance');

        if (!container) {
            return;
        }

        var root      = container.getAttribute('data-url-root') || '';
        var moduleId  = container.getAttribute('data-module-id') || '0';
        var result    = container.querySelector('.wma-mm-maintenance-result');
        var tokenName = (window.Joomla && Joomla.getOptions) ? Joomla.getOptions('csrf.token', '') : '';

        if (!tokenName) {
            setResult('CSRF token non disponibile', 'danger', result);
            return;
        }

        container.querySelectorAll('[data-wma-action]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                run(btn.getAttribute('data-wma-action'), btn, root, moduleId, tokenName, result);
            });
        });
    }

    function run(method, btn, root, moduleId, tokenName, result) {
        btn.disabled = true;
        setResult('…', 'info', result);

        var url = root
            + 'index.php?option=com_ajax'
            + '&module=wmamenumax'
            + '&method=' + encodeURIComponent(method)
            + '&format=json'
            + '&id=' + encodeURIComponent(moduleId)
            + '&' + tokenName + '=1';

        fetch(url, { credentials: 'same-origin' })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                btn.disabled = false;

                if (data && data.success) {
                    setResult(data.message || 'OK', 'success', result);
                } else {
                    setResult((data && data.message) || 'Errore', 'danger', result);
                }
            })
            .catch(function (err) {
                btn.disabled = false;
                setResult('Richiesta fallita: ' + err.message, 'danger', result);
            });
    }

    function setResult(msg, type, result) {
        if (!result) {
            return;
        }

        result.textContent = msg;
        result.className = 'wma-mm-maintenance-result wma-mm-maintenance-result-' + type;
    }

    document.addEventListener('DOMContentLoaded', init);
})();
