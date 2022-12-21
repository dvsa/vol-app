/*/**
 * OLCS.conditionalButton
 *
 * grunt test:single --target=conditionalButton
 */

describe('OLCS.conditionalButton', function() {

  'use strict';

  beforeEach(function() {
    this.component = OLCS.conditionalButton;
  });

  it('should be defined', function() {
    expect(this.component).to.exist;
  });

  it('should be a function', function() {
    expect(this.component).to.be.a('function');
  });

  describe('Given a stubbed DOM with action buttons', function() {

    beforeEach(function() {
      var template = [
        '<form id="stub" method="post" action="/baz">',
          '<div class="govuk-button-group">',
            '<button type="submit" value="Edit" data-label="Edit">Edit</button>',
            '<button type="submit" value="Delete" data-label="Delete">Delete</button>',
          '</div>',
        '</form>'
      ].join('\n');
      this.body = $('body');
      this.body.append(template);
      this.on = sinon.spy($.fn, 'on');
    });

    afterEach(function() {
      this.on.restore();
      $('#stub').remove();
    });

    describe('when initialised with valid options', function() {

      beforeEach(function() {
        this.component({
          form: '#stub',
          predicate: {},
          checkedSelector: '#stub'
        });
      });

      afterEach(function() {
        $(document).off('change');
      });

      it('binds a change handler to the correct selectors', function() {
        expect(this.on.firstCall.args[0]).to.equal('change');
        expect(this.on.firstCall.args[1]).to.equal('#stub');
      });

      describe('and the page is re-rendered', function() {

        beforeEach(function() {
          OLCS.eventEmitter.emit('render');
        });

        it('binds a change handler to the correct selectors', function() {
          expect(this.on.firstCall.args[0]).to.equal('change');
          expect(this.on.firstCall.args[1]).to.equal('#stub');
        });

      });

    }); // when initialised with valid options

    describe('when initialised with label option', function() {

      beforeEach(function() {
        this.component({
          form: '#stub',
          label: '#stub'
        });
      });

      afterEach(function() {
        $(document).off('change');
      });

      it('binds a change handler to the correct selectors', function() {
        expect(this.on.firstCall.args[0]).to.equal('change');
        expect(this.on.firstCall.args[1]).to.equal('#stub');
      });
      
    }); // when initialised with label option

    describe('when initialised with invalid option', function() {

      beforeEach(function() {
        try {
          this.component({
            selector: '#stub',
            label: '#stub'
          });
          OLCS.eventEmitter.emit('render');
        } catch (e) { this.e = e }
      });

      it('should throw the correct error', function() {
        expect(this.e.message).to.equal('\'label\' and \'selector\' are mutually exclusive');
      });

    }); // when initialised with invalid option

  }); // Given a stubbed DOM

    describe('Given a stubbed DOM with action options', function() {

        beforeEach(function() {
            var template = [
                '<form id="stub" method="post" action="/baz">',
                '<div class="govuk-button-group">',
                '<select name="table[action]">',
                '<option value="" disabled="disabled" selected="">Select actions</option>',
                '<option value="add" data-label="Add">Add</option>',
                '<option value="edit" data-label="Edit">Edit</option>',
                '</div>',
                '</form>'
            ].join('\n');
            this.body = $('body');
            this.body.append(template);
            this.on = sinon.spy($.fn, 'on');
        });

        afterEach(function() {
            this.on.restore();
            $('#stub').remove();
        });

        describe('when initialised with valid options', function() {

            beforeEach(function() {
                this.component({
                    form: '#stub',
                    predicate: {},
                    checkedSelector: '#stub'
                });
            });

            afterEach(function() {
                $(document).off('change');
            });

            it('binds a change handler to the correct selectors', function() {
                expect(this.on.firstCall.args[0]).to.equal('change');
                expect(this.on.firstCall.args[1]).to.equal('#stub');
            });

            describe('and the page is re-rendered', function() {

                beforeEach(function() {
                    OLCS.eventEmitter.emit('render');
                });

                it('binds a change handler to the correct selectors', function() {
                    expect(this.on.firstCall.args[0]).to.equal('change');
                    expect(this.on.firstCall.args[1]).to.equal('#stub');
                });

            });

        }); // when initialised with valid options

        describe('when initialised with valid options', function() {

            beforeEach(function() {
                this.component({
                    form: '#stub',
                    predicate: {},
                    checkedSelector: '#stub'
                });
            });

            afterEach(function() {
                $(document).off('change');
            });

            it('binds a change handler to the correct selectors', function() {
                expect(this.on.firstCall.args[0]).to.equal('change');
                expect(this.on.firstCall.args[1]).to.equal('#stub');
            });

            describe('and the page is re-rendered', function() {

                beforeEach(function() {
                    OLCS.eventEmitter.emit('render');
                });

                it('binds a change handler to the correct selectors', function() {
                    expect(this.on.firstCall.args[0]).to.equal('change');
                    expect(this.on.firstCall.args[1]).to.equal('#stub');
                });

            });

        }); // when initialised with valid options

        describe('when initialised with label option', function() {

            beforeEach(function() {
                this.component({
                    form: '#stub',
                    label: '#stub'
                });
            });

            afterEach(function() {
                $(document).off('change');
            });

            it('binds a change handler to the correct selectors', function() {
                expect(this.on.firstCall.args[0]).to.equal('change');
                expect(this.on.firstCall.args[1]).to.equal('#stub');
            });

        }); // when initialised with label option

        describe('when initialised with invalid option', function() {

            beforeEach(function() {
                try {
                    this.component({
                        selector: '#stub',
                        label: '#stub'
                    });
                    OLCS.eventEmitter.emit('render');
                } catch (e) { this.e = e }
            });

            it('should throw the correct error', function() {
                expect(this.e.message).to.equal('\'label\' and \'selector\' are mutually exclusive');
            });

        }); // when initialised with invalid option

    }); // Given a stubbed DOM

});
