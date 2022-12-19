/**
 *
 * grunt test:single --target=accessibleMoreActionsButton
 */
describe('OLCS.accessibleMoreActionsButton', function () {
    'use strict';

    beforeEach(function () {
        this.component = OLCS.accessibleMoreActionsButton;
    });

    it('should be an object', function () {
        expect(this.component).to.be.an('object');
    });

    describe('Given the more actions button is selected', function () {
        beforeEach(function () {
            this.template = [
                '<div id="stub">',
                '<div id="more-actions" tabindex="0" role="button" class="more-actions active">',
                '<div class="more-actions__button">More actions</div>',
                '<div id="more-actions-list" class="more-actions__list" style="display: block;">',
                '<button id="delete" class="more-actions__item govuk-button govuk-button--secondary" name="table[action]" type="submit" value="Delete" data-label="Remove">Remove</button>',
                '<button id="reprint" class=" more-actions__item govuk-button govuk-button--secondary" name="table[action]" type="submit" value="Reprint" data-label="Reprint Disc">Reprint Disc</button>',
                '<button id="transfer" class=" js-require--multiple govuk-button govuk-button--secondary" name="table[action]" type="submit" value="Transfer" data-label="Transfer" disabled="disabled">Transfer</button>',
                '<button id="export" class=" more-actions__item js-disable-crud govuk-button govuk-button--secondary" name="table[action]" type="submit" value="Export" data-label="Export">Export</button>',
                '<button id="show-removed-vehicles" class="govuk-button govuk-button--secondary more-actions__item" name="table[action]" type="submit" value="Show-removed-vehicles" data-label="Show removed vehicles">Show removed vehicles</button>',
                '</div>',
                '</div>',
                '</div>'
            ].join('\n');

            this.body = $("body");
            this.body.append(this.template);

            this.component.init();

            this.deleteButton = document.getElementById('delete');
            this.reprintButton = document.getElementById('reprint');
            this.exportButton = document.getElementById('export');
            this.showRemovedVehiclestButton = document.getElementById('show-removed-vehicles');
            this.moreActionsButton = document.getElementById('more-actions');
            this.moreActionsList = document.getElementById('more-actions-list');

            this.keyDownEvent = function (args) {
                var defaultArgs = {"keycode": 40, "shiftKey": false};
                var overrideObject = $.extend({}, defaultArgs, args);
                var press = jQuery.Event("keydown");
                press.which = overrideObject.keycode;
                press.shiftKey = overrideObject.shiftKey;
                $(".more-actions__list").trigger(press);
            }
        });

        afterEach(function () {
            $('#stub').remove();
        });

        // arrow down key tests
        describe('when the arrow down key is pressed', function () {
            beforeEach(function () {
                this.keyDownEvent();
            });

            describe('and no button is focused in the button list', function () {
                it('it should focus on the first button in the list', function () {
                    expect(this.deleteButton).to.equal(document.activeElement);
                });
            });

            describe('and the first button is focused in the button list', function () {
                beforeEach(function () {
                    this.keyDownEvent();
                });

                it('it should focus on the second button in the list', function () {
                    expect(this.reprintButton).to.equal(document.activeElement);
                });
            });

            describe('and the second button is focused in the button list', function () {
                beforeEach(function () {
                    this.keyDownEvent();
                    this.keyDownEvent();
                });

                it('it should skip the third disabled button and focus on the forth button in the list', function () {
                    expect(this.exportButton).to.equal(document.activeElement);
                });
            });

            describe('and the fourth button is focused in the button list', function () {
                beforeEach(function () {
                    this.keyDownEvent();
                    this.keyDownEvent();
                    this.keyDownEvent();
                });

                it('it should focus on the fifth button in the list', function () {
                    expect(this.showRemovedVehiclestButton).to.equal(document.activeElement);
                });
            });
        });

        // arrow up key tests
        describe('when the arrow up key is pressed', function () {
            beforeEach(function () {
                var args = {"keycode": 38};
                this.keyDownEvent(args);
            });

            describe('and no button is focused in the button list', function () {
                beforeEach(function () {
                    var args = {"keycode": 38};
                    // this.keyDownEvent(args);
                });

                it('it should focus on the fifth button in the list', function () {
                    expect(this.showRemovedVehiclestButton).to.equal(document.activeElement);
                });
            });

            describe('and the fifth button is focused in the button list', function () {
                beforeEach(function () {
                    var args = {"keycode": 38};
                    this.keyDownEvent(args);
                });

                it('it should focus on the fourth button in the list', function () {
                    expect(this.exportButton).to.equal(document.activeElement);
                });
            });

            describe('and the fourth button is focused in the button list', function () {
                beforeEach(function () {
                    var args = {"keycode": 38};
                    this.keyDownEvent(args);
                    this.keyDownEvent(args);
                });

                it('it should skip the disabled button in the list and focus on the second button', function () {
                    expect(this.reprintButton).to.equal(document.activeElement);
                });
            });

            describe('and the second button is focused in the button list', function () {
                beforeEach(function () {
                    var args = {"keycode": 38};
                    this.keyDownEvent(args);
                    this.keyDownEvent(args);
                    this.keyDownEvent(args);
                    this.keyDownEvent(args);
                });

                it('it should focus on the first button in the list', function () {
                    expect(this.deleteButton).to.equal(document.activeElement);
                });
            });
        });


        // tab (forward) key tests
        describe('when the fifth (and last) button is focused in the button list', function () {
            beforeEach(function () {
                this.keyDownEvent();
                this.keyDownEvent();
                this.keyDownEvent();
                this.keyDownEvent();
            });

            describe('and the tab button is pressed', function () {
                beforeEach(function () {
                    var args = {"keycode": 9};
                    this.keyDownEvent(args);
                });

                it('the more actions button should deactivate', function () {
                    expect(this.moreActionsButton.classList.contains('active')).to.equal(false);
                    expect(this.moreActionsList.hasAttribute('style')).to.equal(false);
                });
            });
        });

        // tab backwards tests
        describe('when no button is focused in the button list', function () {

            describe('and the user tabs backwards', function () {
                beforeEach(function () {
                    var args = {"keycode": 9, "shiftKey": true};
                    this.keyDownEvent(args);
                });

                it('the more actions button should deactivate', function () {
                    expect(this.moreActionsButton.classList.contains('active')).to.equal(false);
                    expect(this.moreActionsList.hasAttribute('style')).to.equal(false);
                });
            });
        });

    });
});
