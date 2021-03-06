/**
 * Autosave functionality JavaScript part
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at http://mozilla.org/MPL/2.0/.
 *
 * @category  phpMyFAQ
 * @package   JavaScript
 * @author    Anatoliy Belsky <ab@php.net>
 * @copyright 2012-3013 phpMyFAQ Team
 * @license   http://www.mozilla.org/MPL/2.0/ Mozilla Public License Version 2.0
 * @link      http://www.phpmyfaq.de
 * @since     2012-07-07
 */

/*global clearInterval: false, clearTimeout: false, document: false, event: false, frames: false, history: false, Image: false, location: false, name: false, navigator: false, Option: false, parent: false, screen: false, setInterval: false, setTimeout: false, window: false, XMLHttpRequest: false */

$(document).ready(function () {
    "use script";
    $(window).unload(function () {
        if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor !== null) {
            if (tinyMCE.activeEditor.isDirty()) {
                var chk = confirm('Do you want to save the article before navigating away?');

                if (chk) {
                    pmfAutosave();
                }
            }
        }
    });

    if (typeof pmfAutosaveInterval !== 'undefined') {
        setInterval('pmfAutosave();', pmfAutosaveInterval * 1000);
    }

    /**
     * Post autosave data via AJAX.
     *
     * @return void
     */
    function pmfAutosave() {
        var ed = tinyMCE.activeEditor;
        if (ed.isDirty()) {
            var formData = {};
            formData.revision_id = $('#revision_id').attr('value');
            formData.record_id = $('#record_id').attr('value');
            formData.csrf = $('[name="csrf"]').attr('value');
            formData.openQuestionId = $('#openQuestionId').attr('value');
            formData.question = $('#question').attr('value');
            formData.answer = ed.getContent();
            formData.keywords = $('#keywords').attr('value');
            formData.tags = $('#tags').attr('value');
            formData.author = $('#author').attr('value');
            formData.email = $('#email').attr('value');
            formData.lang = $('#lang').attr('value');
            formData.solution_id = $('#solution_id').attr('value');
            formData.active = $('input:checked:[name="active"]').attr('value');
            formData.sticky = $('#sticky').attr('value');
            formData.comment = $('#comment').attr('value');
            formData.grouppermission = $('[name="grouppermission"]').attr('value');
            formData.userpermission = $('[name="userpermission"]').attr('value');
            formData.restricted_users = $('[name="restricted_users"]').attr('value');
            formData.dateActualize = $('#dateActualize').attr('value');
            formData.dateKeep = $('#dateKeep').attr('value');
            formData.dateCustomize = $('#dateCustomize').attr('value');
            formData.date = $('#date').attr('value');

            $.ajax({
                url: pmfAutosaveAction(),
                type: 'POST',
                data: formData,
                success: function (r) {
                    var resp = $.parseJSON(r);

                    $('#saving_data_indicator').html(resp.msg);

                    ed.isNotDirty = true;

                    $('#record_id').attr('value', resp.record_id);
                    $('#revision_id').attr('value', resp.revision_id);
                    /* XXX update more places on the page according to the new saved data */
                }
            });
        }
    }

    /**
     * Produce AJAX autosave action.
     *
     * @return string
     */
    function pmfAutosaveAction() {
        var act,
            fa = $("#faqEditor").attr("action");

        act = "?action=ajax&ajax=autosave&" + fa.substr(1).replace(/action=/, "do=");

        return act;
    }

    function getYesNoVal(selector) {

    }

});