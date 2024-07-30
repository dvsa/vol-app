var OLCS = OLCS || {};

/**
 * WYSIWYG
 *
 * Small wrapper around TinyMCE plugin to tidy up some of the
 * formatting/code it spews out
 */

OLCS.wysiwyg = (function(document, $, undefined) {

  'use strict';

  return function init() {

    OLCS.eventEmitter.on('render', function() {

      if (typeof(tinymce) === 'undefined') {
        return;
      }

      $('.tinymce').each(function() {
        $(this).tinymce({
          menubar : false,
          statusbar : false,
          document_base_url: '../tinymce/',
          spellchecker_languages : '+English=en',
          spellchecker_language : 'en',
          spellchecker_rpc_url: 'index.php',
          spellchecker_report_misspellings : true,
          forced_root_blocks: false,
          height : 260,
          content_css : '../tinymce/skins/lightgray/custom.css',
          style_formats: [
            {title: 'Header 1', format: 'h1'},
            {title: 'Header 2', format: 'h2'},
            {title: 'Header 3', format: 'h3'}
          ],
          plugins: [
            'lists charmap',
            'searchreplace',
            'paste spellchecker'
          ],
          paste_postprocess: function(plugin, args){
            var elements = args.node.getElementsByTagName('*');
            for(var i = 0; i < elements.length; i ++){
              elements[i].className = '';
            }
          },
          paste_as_text: false,
          toolbar: 'styleselect | bold italic underline | bullist numlist | indent outdent | spellchecker',
          init_instance_callback: function() {
            lockActions();
          },
          setup: function(editor) {
            editor.on('keyup', function() {
              if (tinymce.activeEditor.getContent()) {// jshint ignore:line
                unlockActions();
              } else {
                lockActions();
              }
            });
          }
        });

        // If the editor was initialised in a modal, we need to remove it
        // when the modal closes
        OLCS.eventEmitter.on('hide:modal', function() {
          tinymce.EditorManager.editors = []; // jshint ignore:line
        });
      });

      // Upon init, we need to lock submit buttons until something actually
      // gets written into tinymce editor, to prevent issues with validation
      function lockActions() {
        $('.modal').find('.actions-container')
          .children().first() // the save/submit button
          .addClass('disabled').prop('disabled', true);
      }

      function unlockActions() {
        $('.modal').find('.actions-container')
          .children().first() // the save/submit button
          .removeClass('disabled').prop('disabled', false);
      }

    });

  };

}(document, window.jQuery));
