/**
 * OLCS.modalLink
 *
 * grunt test:single --target=submissionRecommendation
 */

describe('OLCS.submissionRecommendation', function () {

    'use strict';

    beforeEach(function () {
        this.component = OLCS.submissionRecommendation;
    });


    it('should be an object', function () {
        expect(this.component).to.be.an('object');
    });

    describe('Given a stubbed DOM', function () {
        beforeEach(function () {
            this.template = [
                '<div id="stub">',
                '<select id="sourceId" multiple="multiple">',
                '<option value="1">option 1</option>',
                '<option value="2">option 2</option>',
                '<option value="3">option 3</option>',
                '<option value="4">option 4</option>',
                '<option value="5">option 5</option>',
                '<option value="6">option 6</option>',
                '<option value="theTarget">option 7</option>',
                '<option value="8">option 8</option>',
                '</select>',
                '<select id="destId" multiple="multiple">',
                '<option value="1" data-in-office-revokation="N">option 1</option>',
                '<option value="2" data-in-office-revokation="N">option 2</option>',
                '<option value="3" data-in-office-revokation="N">option 3</option>',
                '<option value="4" data-in-office-revokation="N">option 4</option>',
                '<option value="5" data-in-office-revokation="N">option 5</option>',
                '<option value="6" data-in-office-revokation="N">option 6</option>',
                '<option value="7" data-in-office-revokation="N">option 7</option>',
                '<option value="8" data-in-office-revokation="Y">option 8</option>',
                '</select>',
                '</div>'
            ].join('\n');

            this.body = $("body");
            this.body.append(this.template);

        });

        afterEach(function () {
            $('#stub').remove();
        });

        describe("when initialised with appropriate options", function () {
            beforeEach(function () {
                this.jQueryOnStub = sinon.stub($.fn, 'on');
                this.component.addChangeEvent({
                    source: "#sourceId",
                    dest: "#destId",
                    target: "theTarget"
                });
            });

            afterEach(function () {
                $(document).off("change");
                this.jQueryOnStub.restore();
            });


            it("should add a change event handler", function () {
                expect(this.jQueryOnStub.callCount).to.equal(1);
            });

        });

        describe("when selecting the target and calling the change event", function () {
            beforeEach(function () {
                this.sourceList = document.getElementById('sourceId');
                this.targetList = document.getElementById('destId');

                this.sourceList.selectedIndex = 6;
                this.component.removeRevokations({
                    source: "#sourceId",
                    dest: "#destId",
                    target: "theTarget"
                });
            });

            it("should remove all data-in-office-revokation=N options from the taget list", function () {
                expect(this.targetList.options.length).to.equal(1);
            });

            describe("when changing the selection back", function () {
                beforeEach(function () {
                    this.sourceList.selectedIndex = 1;
                    this.component.removeRevokations({
                        source: "#sourceId",
                        dest: "#destId",
                        target: "theTarget"
                    });
                });

                it("should restore the origial options list", function () {
                    expect(this.targetList.options.length).to.equal(8);
                })
            });

        });

        describe("when selecting ta different target and calling the change event", function () {
            beforeEach(function () {
                this.sourceList = document.getElementById('sourceId');
                this.targetList = document.getElementById('destId');

                this.sourceList.selectedIndex = 1;
                this.component.removeRevokations({
                    source: "#sourceId",
                    dest: "#destId",
                    target: "theTarget"
                });
            });

            it("should not remove any options from the taget list", function () {
                expect(this.targetList.options.length).to.equal(8);
            });

            describe("when changing the selection back", function () {

            })

        });


    }); // Given a stubbed DOM

});
