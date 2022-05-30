YUI.add('moodle-atto_molstructure-button', function (Y, NAME) {

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/*
 * @package    atto_molstructure
 * @copyright  2022 Louis Plyer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * @module moodle-atto_molstructure-button
 */
/**
 * Atto text editor structure plugin.
 *
 * @namespace M.atto_molstructure
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */
var COMPONENTNAME = 'atto_molstructure',
    IFRSOURCE = M.cfg.wwwroot + '/lib/editor/atto/plugins/molstructure/canvas.html',
    IFID = 'canvas',
    LOGNAME = 'atto_molstructure',
    SUBMITID = 'submit',
    CSS = {
        INPUTSUBMIT: 'atto_media_urlentrysubmit',
        HGT: 'height: 75vh;',
        WDT: 'width: 48vw;'
    },

    TEMPLATE = '' +
        '<iframe src="{{isource}}" id="{{iframeID}}" style="{{CSS.HGT}}{{CSS.WDT}}">' +
        '<p>{{get_string "iframeprb" component}}</p>' +
        '</iframe>' +

        '<div style="text-align:center">' +

        '<button class="{{CSS.INPUTSUBMIT}}" id ="{{submitID}}" style="{{selectalign}}">' +
        '{{get_string "insert" component}}' +
        '</button>' +
        
        '</div>';

Y.namespace('M.atto_molstructure').Button = Y.Base.create('button', Y.M.editor_atto
    .EditorPlugin, [], {
    _usercontextid: null,
    _filename: null,
    /**
     * Initialize the button
     *
     * @method Initializer
     */
    initializer: function (config) {
        this._usercontextid = config.usercontextid;
        this._filename = new Date().getTime();
        var host = this.get('host');
        var options = host.get('filepickeroptions');
        if (options.image && options.image.itemid) {
            this._itemid = options.image.itemid;
        } else {
            return;
        }

        // If we don't have the capability to view then give up.
        if (this.get('disabled')) {
            return;
        }

        // Add the structure icon/buttons
        this.addButton({
            icon: 'icon',
            iconComponent: 'atto_molstructure',
            buttonName: 'icon',
            callback: this._displayDialogue,
            callbackArgs: 'icon'
        });
    },

    /**
     * Display the structure Dialogue
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function (e, clickedicon) {
        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
            height: '90vh',
            width: '50vw',
            focusAfterHide: clickedicon
        });

        e.preventDefault();

        // When dialog becomes invisible, reset it. This fixes problems with multiple editors per page.
        dialogue.after('visibleChange', function() {
            var attributes = dialogue.getAttrs();

            if (attributes.visible === false) {
                setTimeout(function() {
                    dialogue.reset();
                }, 5);
            }
        });

        // Append buttons to iframe.
        var bodycontent = this._getFormContent(clickedicon);

        // Set to bodycontent.
        dialogue.set('bodyContent', bodycontent);
        document.getElementById(IFID).src = IFRSOURCE;
        dialogue.centerDialogue();
        dialogue.show();
        this.markUpdated();
    },

    /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getFormContent
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getFormContent: function (clickedicon) {
        var template = Y.Handlebars.compile(TEMPLATE),
            content = Y.Node.create(template({
                elementid: this.get('host').get('elementid'),
                CSS: CSS,
                component: COMPONENTNAME,
                clickedicon: clickedicon,
                isource: IFRSOURCE,
                iframeID: IFID,
                submitID: SUBMITID
            }));
        this._form = content;
        this._form.one('.' + CSS.INPUTSUBMIT).on('click', this._getImgURL,
            this);
        this._form.one('#' + IFID).on('load', this._changeLangString,
            this);
        return content;
    },

    _uploadFile: function (filedata, recid, filename) {
        var xhr = new XMLHttpRequest();
        var ext = "png";
        // file received/failed
        xhr.onreadystatechange = (function () {
            return function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        var resp = xhr.responseText;
                        var start = resp.indexOf(
                            "success<error>");
                        if (start < 1) {
                            return;
                        }
                    }
                }
            };
        })(this);
        var params = "datatype=uploadfile";
        params += "&paramone=" + encodeURIComponent(filedata);
        params += "&paramtwo=" + ext;
        params += "&paramthree=image";
        params += "&requestid=" + filename;
        params += "&contextid=" + this._usercontextid;
        params += "&component=user";
        params += "&filearea=draft"; // change filearea
        params += "&itemid=" + this._itemid;
        xhr.open("POST", M.cfg.wwwroot +
            "/lib/editor/atto/plugins/molstructure/structurefilelib.php",
            true);
        xhr.setRequestHeader("Content-Type",
            "application/x-www-form-urlencoded");
        xhr.setRequestHeader("Cache-Control", "no-cache");
        //xhr.setRequestHeader("Connection", "close");
        xhr.send(params);
    },
    _changeLangString: function (e) {
        e.preventDefault();
        function update_lang_string() {
            var iframBody = $('#' + IFID);
            var buttoncontent = iframBody.contents();

            var button = buttoncontent.find('#button-size-button');
            button[0].firstChild.data =(M.util.get_string('resize', COMPONENTNAME));

            var height_input = buttoncontent.find('#label_height_input_molstructure');
            height_input[0].firstChild.data =(M.util.get_string('height', COMPONENTNAME));

            var width_input = buttoncontent.find('#label_width_input_molstructure');
            width_input[0].firstChild.data =(M.util.get_string('width', COMPONENTNAME));
        }
        update_lang_string();
    },

    _getImgURL: function (e) {
        e.preventDefault();

        var filename = new Date().getTime() + '-' + Math.round(Math.random() * 10000);
        var thefilename = "upfile_" + filename + ".png";
        var divContent = '';
        var referringpage = this;

        function test(thefilename) {
            // Getting the viewer canvas.
            var iframBody = $('#' + IFID);
            var buttoncontent = iframBody.contents();
            var button = buttoncontent.find('#sketcher-viewer-atto');
            var img_URL = button[0].toDataURL('image/svg');
            // console.log(img_URL);
            referringpage._uploadFile(img_URL, "1", filename);
            var wwwroot = M.cfg.wwwroot;
            var filesrc = wwwroot + '/draftfile.php/' + referringpage._usercontextid +
                '/user/draft/' + referringpage._itemid + '/' + thefilename;
            divContent = "<img name=\"pict\" src=\"" + filesrc + "\" alt=\"ChemDoodle PNG\"/>";
            referringpage.editor.focus();
            referringpage.get('host').insertContentAtFocusPoint(divContent);
            referringpage.markUpdated();
        }

        test(thefilename);
        this.getDialogue({
            focusAfterHide: null
        }).hide();
    }

}, {
    ATTRS: {
        disabled: {
            value: false
        },
        usercontextid: {
            value: null
        }
    }
});


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
