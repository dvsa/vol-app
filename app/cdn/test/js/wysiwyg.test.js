/**
 * OLCS.url
 *
 * grunt test:single --target=wysiwyg
 */
describe('OLCS.wysiwyg', function() {

  'use strict';

  it('should be defined', function() {
    expect(OLCS.wysiwyg).to.exist;
  });

  it('should expose the TinyMCE API', function() {
    expect(typeof(tinymce)).to.not.be('undefined');
  });

  describe('when initialised with default options', function() {
    beforeEach(function() {
      $('body').append('<textarea id="stub" class="tinymce"></textarea>');
      OLCS.wysiwyg();
      OLCS.eventEmitter.emit('render');
    });

    afterEach(function() {
      $('#stub').remove();
      tinyMCE.remove();
    });

    it('should successfullly create a TinyMCE instance', function() {
      expect($('.mce-tinymce').length).to.equal(1);
      expect($('.mce-tinymce iframe').length).to.equal(1);
      expect(tinymce.EditorManager.editors.length).to.equal(1);
    });
  });

  describe('when initialised via AJAX inside a modal', function() {

    beforeEach(function() {
      this.xhr = sinon.useFakeXMLHttpRequest();
      this.requests = [];
      this.xhr.onCreate = function(xhr) {
          this.requests.push(xhr);
      }.bind(this);

      $('body').append('<a id="stub" class="js-modal-ajax" href="/foo">Click me</a>');
      OLCS.wysiwyg();
    });

    afterEach(function() {
      this.xhr.restore();
      $('#stub').remove();
      tinyMCE.remove();
    });
    
    describe('when clicking the target action', function() {
      beforeEach(function() {
        OLCS.modalLink({trigger: '.js-modal-ajax'});
        $('#stub').click();
      });

      afterEach(function(){

        $(document).off('click');
      });


      describe("Given the request returns a tinyMCE input", function() {
        beforeEach(function(){
          //we also need to fake the authentication response or the modal will not show. 
          for(var i = 0; i < this.requests.length; i++){
            if(this.requests[i].url.indexOf("/auth/validate") > -1){
              var authResponse = {"valid":true};
              var authResonseJson = JSON.stringify(authResponse);
              this.requests[i].respond(200, { 'Content-Type': 'text/json' }, authResonseJson);
            } else if(this.requests[i].url === "/foo") {
              var htmlResponse = '<textarea class="tinymce"></textarea>' + 
              '<fieldset class="actions-container" data-group="form-actions">' +
              '<button type="button" name="form-actions&#x5B;submit&#x5D;" value="" id="submit">Save</button>' +
              '<button type="button" name="form-actions&#x5B;cancel&#x5D;" >Cancel</button></fieldset>' +
              '</form></div>'
;
              this.requests[i].respond(200, { 'Content-Type': 'text/html' }, htmlResponse);
            }
          }
          
        });

        it('should create a modal', function(){
            expect($('.modal').length).to.equal(1);
          });


        it('should insert the response', function(){
            expect($('.tinymce').length).to.equal(1);
          });

        it('should successfully create a TinyMCE instance', function() {
          expect($('.mce-tinymce').length).to.equal(1);
          expect($('.mce-tinymce iframe').length).to.equal(1);
          expect(tinymce.EditorManager.editors.length).to.equal(1);
        });

        it('the modal should have a close button', function(){
          expect($('.modal__close').length).to.be.equal(1);
        });

        it('the save button should be disabled', function(){
          expect(document.getElementById('submit').disabled).to.be(true);
        });

        describe("when entering some text", function(){

          beforeEach(function(){
            var editor = tinymce.EditorManager.editors[0];
            editor.setContent("I am some content");
            editor.fire("keyUp");
          });

          it('should enable the save button', function(){
            expect(document.getElementById('submit').disabled).to.be(false);
          });

        });

        describe("when closing the modal", function(){

          beforeEach(function(){
            $('.modal__close').click();
          });

          it('should remove tinyMCE editors', function(){
            expect(tinymce.EditorManager.editors.length).to.equal(0);
          });

        });
      });
    });
  });

});