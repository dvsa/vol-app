/**
 * OLCS.disableForm
 * 
 * grunt test:single --target=disableForm
 */
describe('OLCS.disableForm', function() {

  'use strict';

  beforeEach(function() {
    this.component = OLCS.disableForm;
  });

  it('Should be a function', function() {
    expect(this.component).to.be.a('function');
  });

  describe('Given a form with multiple action buttons', function() {

    beforeEach(function() {
      $('body').append([
        '<form id="stub">' +
          '<input  id="input-submit"  type="submit"  class="submit" value="Submit">' +
          '<button id="button-submit" type="submit"  class="submit">Submit</button>' +
          '<a      id="anchor-submit" href="#submit" class="submit action-primary">Close</a>' +
        '</form>' +
        '<button type="submit" class="fake-submit">Fake Submit</button>'
      ].join("\n"));

      $('#stub').on('submit', function(e) {
         e.preventDefault();
      });
    });

    afterEach(function() {
      $('#stub').remove();
    });

    describe('When invoked using basic options', function() {

      beforeEach(function() {
        this.component({
          container : '#stub',
          loadingText: 'Loading...'
        });
      });

      describe('When a regular submit button is clicked', function() {
        beforeEach(function() {
          $('#button-submit').click();
        });

        it('The action buttons should have the "disabled" class', function() {
          expect($('#stub .submit').hasClass('disabled')).to.be(true);
        });

        it('The clicked button should have correct loading text', function() {
          expect($('#button-submit').html()).to.be('Loading...');
        });

        it('All other submit buttons should remain unaffected', function() {
          expect($('.fake-submit').hasClass('disabled')).to.be(false);
        });
      });

      describe('When an input submit button is clicked', function() {
        beforeEach(function() {
          $('#input-submit').click();
        });

        it('The action buttons should have the "disabled" class', function() {
          expect($('#stub .submit').hasClass('disabled')).to.be(true);
        });

        it('The clicked button should have correct loading text', function() {
          expect($('#input-submit').val()).to.be('Loading...');
        });
      });

    });


    describe('When invoked without loading text', function() {

      beforeEach(function() {
        this.component({
          container : '#stub',
          loadingText : ''
        });
      });

      describe('When a regular submit button is clicked', function() {
        beforeEach(function() {
          $('#button-submit').click();
        });

        it('The action buttons should have the "disabled" class', function() {
          expect($('#stub .submit').hasClass('disabled')).to.be(true);
        });

        it('The clicked button should not have altered text', function() {
          expect($('#button-submit').html()).to.be('Submit');
        });

        it('All other submit buttons should remain unaffected', function() {
          expect($('.fake-submit').hasClass('disabled')).to.be(false);
        });
      });

    });

  });

});