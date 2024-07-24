/**
 * OLCS.generateCSSSelector
 *
 * grunt test:single --target=generateCSSSelector
 */

describe("OLCS.generateCSSSelector", function() {
    "use strict";

    beforeEach(function() {
        this.component = OLCS.generateCSSSelector;
    });

    it("should be defined", function() {
        expect(this.component).to.exist;
    });

    describe('Given a stubbed DOM', function() {
        beforeEach(function() {
            $('body').append([
                '<tbody id="tbody">',
                '<tr id="tr1">',
                '<td><a href=#></a></td>',
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

        describe('When a an element with name attribute and id is passed', function() {
            beforeEach(function() {
                this.result = this.component($('#tr1 input'));
            });

            it("returns the selector string made by tag name and id", function() {
                expect(this.result).to.be.an("string");
                expect(this.result).to.be("input[id='cb1']");
            });

        });

        describe('When a an element with name attribute but no id is passed', function() {
            beforeEach(function() {
                this.result = this.component($('#tr2 input'));
            });

            it("returns the selector string made by tag name and name attribute", function() {
                expect(this.result).to.be.an("string");
                expect(this.result).to.be("input[name='cb2']");
            });

        });

        describe('When a an element with no name attribute and no id is passed', function() {
            beforeEach(function() {
                this.result = this.component($('#tr2 a'));
            });

            it("returns the selector string made by navugating up the dom until an id or name is found", function() {
                expect(this.result).to.be.an("string");
                expect(this.result).to.be("tr[id='tr2']>td:nth-child(1)>a");
            });

        });
    });


});
