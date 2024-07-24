/**
 * OLCS.nextFocusableElement
 *
 * grunt test:single --target=nextFocusableElements
 */

describe("OLCS.nextFocusableElements", function() {
    "use strict";

    beforeEach(function() {
        this.component = OLCS.nextFocusableElements;
    });

    it("should be defined", function() {
        expect(this.component).to.exist;
    });

    describe('Given a stubbed DOM', function() {
        beforeEach(function() {
            $('body').append([
                '<tbody id="tbody">',
                '<tr id="tr1">',
                '<td><a href=#></a><p><a href=# id="link1"></a></p></td>',
                '<td><input type="checkbox" id="cb1" name="cb1"></td>',
                '</tr>',
                '<tr id="tr2">',
                '<td><a href=#></a></td>',
                '<td><input type="checkbox" name="cb2"></td>',
                '</tr>',
                '</tbody>'
            ].join('\n'));
        });

        afterEach(function() {
            $('#tbody').remove();
        });

        describe('When a an element is passed', function() {
            beforeEach(function() {
                this.result = this.component($('#tr1 a'));
            });

            it("returns an array with the next focusable elements", function() {
                expect(this.result).to.be.an("object");
                expect(this.result[0].id).to.be('link1');
                expect(this.result[1].id).to.be('cb1');
            });
        });

    });


});
