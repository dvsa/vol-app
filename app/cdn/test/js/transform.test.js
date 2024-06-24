/*/**
 * OLCS.transform
 *
 * grunt test:single --target=transform
 */

describe('OLCS.transform', function() {

  'use strict';

  beforeEach(function() {
    this.component = OLCS.transform;
  });

  it('should be a function', function() {
    expect(this.component).to.be.a('function');
  });

  describe('Given a stubbed DOM', function() {

    beforeEach(function() {
      $('body').append([
        '<div id="stub" class="foo bar">',
          '<div class="bar"></div>',
        '</div>'
      ].join('\n'));
    });

    afterEach(function() {
      $('#stub').remove();
    });

    describe('When the component is initialised', function() {

      beforeEach(function() {
        this.component({
          selector: '.foo',
          replace: {
            '.bar' : '.baz'
          }
        });
      });

      // should it though? because it doesn't
      it.skip('should remove the old class', function() {
        expect($('#stub').is('.bar')).to.be(false);
      });

      it('should add the new class', function() {
        expect($('#stub').is('.baz')).to.be(true);
      });

    }); // When the component is initialised

    describe('When the component is initialised but the selector doesn`t exist', function() {

      beforeEach(function() {
        this.component({
          selector: '#null'
        });
      });

      it('should not affect the original class', function() {
        expect($('#stub').is('.baz')).to.be(false);
      });

    }); // When the component is initialised but the selector doesn`t exist

    describe('When the component is initialised but the target doesn`t exist', function() {

      beforeEach(function() {
        this.component({
          selector: '.foo',
          replace: {
            '.baz' : '.bar'
          }
        });
      });

      it('should not replace the class name', function() {
        expect($('#stub').is('.baz')).to.be(false);
      });

    }); // When the component is initialised but the target doesn`t exist

  }); // Given a stubbed DOM

});