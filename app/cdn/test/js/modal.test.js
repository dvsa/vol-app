/**
 * OLCS.modal
 *
 * grunt test:single --target=modal
 */

describe('OLCS.modal', function() {

  'use strict';

  beforeEach(function() {
    this.component = OLCS.modal;
  });

  it('should be defined', function() {
    expect(this.component).to.exist;
  });

  it('should expose the correct public interface', function() {
    expect(this.component.show).to.be.a('function');
    expect(this.component.hide).to.be.a('function');
    expect(this.component.isVisible).to.be.a('function');
    expect(this.component.updateBody).to.be.a('function');
  });

  describe('show', function() {
    beforeEach(function() {
      this.component.show('body here', 'title here');
    });

    afterEach(function() {
      this.component.hide();
    });

    it('shows the component', function() {
      expect(this.component.isVisible()).to.be(true);
    });

    it('shows the modal overlay', function() {
      expect($('.overlay').is(':visible')).to.be(true);
    });

    it('shows the modal wrapper', function() {
      expect($('.modal__wrapper').is(':visible')).to.be(true);
    });

    it('applies the correct title', function() {
      expect($('.modal__title').text()).to.equal('title here');
    });

    it('applies the correct body', function() {
      expect($('.modal__content').text()).to.equal('body here');
    });

    it('should have a close button', function() {
      expect($('.modal__close').length).to.be(1);
    });

    describe('When clicking the modal close button', function() {
      beforeEach(function() {
        this.spy = sinon.stub(this.component, 'hide');
        $('.modal__close').trigger('click');
      });

      afterEach(function() {
        this.spy.restore();
      });

      it('invokes the modal’s hide method', function() {
        expect(this.spy.called).to.be(true);
      });
    });

    describe('when pressing the Esc button', function() {
      beforeEach(function() {
        var e = jQuery.Event('keyup');
        e.keyCode = 27;
        $(document).trigger(e);
      });

      it('hides the modal', function() {
        expect(this.component.isVisible()).to.be(false);
      });
    });
  }); // show

  describe('Show with a "close-trigger" data-attribute', function() {
    beforeEach(function() {
      $(document).on('click', '.action', function() {
        $('body').append('<div id="foo"></div>');
      });
      var $content = [
        '<form data-close-trigger=".action">',
          '<div class="action"></div>',
        '</form>',
      ].join('\n');
      this.component.show($content);
      this.component.hide();
    });

    it('should not close the component', function() {
      expect(this.component.isVisible()).to.be(true);
    });
    
    it('should trigger a click of the ".action" element', function() {
      expect($('#foo').length).to.be(1);
    });
  }); // Show with a "close-trigger" data-attribute

  describe('show without a passed title', function() {
    beforeEach(function() {
      this.component.show('body here');
    });

    afterEach(function() {
      this.component.hide();
    });

    it('shows the modal overlay', function() {
      expect($('.overlay').is(':visible')).to.be(true);
    });

    it('shows the modal wrapper', function() {
      expect($('.modal__wrapper').is(':visible')).to.be(true);
    });

    it('applies the correct title', function() {
      expect($('.modal__title').text()).to.equal('');
    });

    it('applies the correct body', function() {
      expect($('.modal__content').text()).to.equal('body here');
    });
  }); // show without passed title

  describe('hide', function() {
    beforeEach(function() {
      this.eventSpy = sinon.spy(OLCS.eventEmitter, 'emit');
      this.component.hide();
    });

    afterEach(function() {
      this.eventSpy.restore();
    });

    it('hides the modal overlay', function() {
      expect($('.overlay').is(':visible')).to.be(false);
    });

    it('hides the modal wrapper', function() {
      expect($('.modal__wrapper').is(':visible')).to.be(false);
    });

    it('emits the correct event', function() {
      expect(this.eventSpy.firstCall.args[0]).to.equal('hide:modal');
    });
  }); // hide

  describe('Simulate mobile experience', function() {
    beforeEach(function() {
      $('body').append([
        '<div class=modal__wrapper>',
          '<input type="text" />',
        '</div>'
      ].join('\n'));
    });

    describe('When an input element is focused', function() {
      beforeEach(function() {
        $('input').focus();
      });
      it('centers the modal wrapper', function() {
        expect($('.modal__wrapper').css('position')).to.equal('absolute');
      });
    });

    describe('When an input element is blurred', function() {
      beforeEach(function() {
        $('input').blur();
      });
      it('centers the modal wrapper', function() {
        expect($('.modal__wrapper').css('position')).to.equal('static');
      });
    });
  }); // Simulate mobile experience

  describe('When invoked when a modal is already open', function() {
    beforeEach(function() {
      $('body').append([
        '<div class="overlay" style="display:none;"></div>'
      ].join('\n'));
      this.component.show();
    });

    afterEach(function() {
      this.component.hide();
    });

    it('hides the old modal and shows the new one', function() {
      expect($('.overlay').is(':visible')).to.be(true);
    });
  }); // When invoked when a modal is already open

});
