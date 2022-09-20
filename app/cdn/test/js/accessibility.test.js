/**
 * OLCS.accessibility
 *
 * grunt test:single --target=accessibility
 */

describe('OLCS.accessibility', function() {

  'use strict';

  beforeEach(function() {
    this.component = OLCS.accessibility;
  });

  it('Should be a function', function() {
    expect(this.component).to.be.a('function');
  });

  describe('Auto focus on error messages', function() {

    beforeEach(function() {
      $('body').append([
        '<div id="stub" class="validation-summary">',
        '</div>'
      ].join('\n'));
    });

    afterEach(function() {
      $('#stub').remove();
    });

    describe('When the component is initialised', function() {

      beforeEach(function() {
        this.component({errorContainer:'#stub'});
        OLCS.eventEmitter.emit('render');
      });

      it('The target should be focusable by script', function() {
        expect($('#stub').attr('tabindex')).to.be('-1');
      });

      it('The target should be focused', function() {
        expect(document.activeElement.id).to.be('stub');
      });

      it('The target should be scrolled to', function() {
        expect(window.location.hash).to.be('#stub');
      });

    }); // When the component is initialised

  }); // Given a page with form errors present

  describe('Skip to main content', function() {

    beforeEach(function() {
      $('body').append([
        '<a id="stub" class="govuk-visually-hidden" href="#main">Skip to Main Content</a>',
        '<div id="main"></div>'
      ].join('\n'));
    });

    afterEach(function() {
      $('#stub').remove();
      $('#main').remove();
    });

    describe('When the component is initialised', function() {

      beforeEach(function() {
        this.component({
          skipTrigger : '#stub',
          skipTarget  : '#main'
        });
      });

      describe('And the trigger element is clicked', function() {

        beforeEach(function() {
          $('#stub').click();
        });

        it('The target should be removed from the tabindex', function() {
          expect($('#main').attr('tabindex')).to.be('-1');
        });

        it('The target should be focused', function() {
          expect(document.activeElement.id).to.be('main');
        });

      }); // And the trigger element is clicked

    }); // When the component is initialised

  }); // Skip to main content

  describe('Ensure appropriate element focusing', function() {

    beforeEach(function() {
      $('body').append([
        '<label id="stub">',
          '<input id="checkbox-stub" type="checkbox" />',
        '</label>'
      ].join('\n'));
    });

    afterEach(function() {
      $('#stub').remove();
    });

    describe('When the component is initialised', function() {

      beforeEach(function() {
        this.component();
        OLCS.eventEmitter.emit('render');
      });

      it('The label should have a tabindex of 0', function() {
        expect($('#stub').attr('tabindex')).to.be('0');
      });

      it('The input should have a tabindex of -1', function () {
        expect($('#checkbox-stub').attr('tabindex')).to.be('-1');
      });

      describe('And the trigger element is focused', function() {

        beforeEach(function() {
          $('#stub').triggerHandler('focus');
        });

        it('The target should be focused', function() {
          expect($('#stub').attr('tabindex')).to.be('-1');
          expect($('#stub').hasClass('focused')).to.be(true);
        });

      });

      describe('And the trigger element is blurred', function() {

        beforeEach(function() {
          $('#stub > input').blur();
        });

        it('The target should be focused', function() {
          expect($('#stub').attr('tabindex')).to.be('0');
          expect($('#stub').hasClass('focused')).to.be(false);
        });

      });

    }); // When the component is initialised

  }); // Ensure appropriate element focusing

}); // OLCS.accessibility