/**
 * OLCS.conditionallyDisableButtons
 *
 * grunt test:single --target=conditionallyDisableButtons
 */

describe("OLCS.conditionallyDisableButtons", function() {
    "use strict";

    beforeEach(function() {
        this.component = OLCS.conditionallyDisableButtons;
    });

    it('should be defined', function() {
        expect(this.component).to.exist;
    });

    it('should be a function', function() {
        expect(this.component).to.be.a('function');
    });

    describe('Given a stubbed DOM', function() {

        beforeEach(function() {
            var template = [
                '<form id="stub" method="post" action="/baz">',
                '<div class="govuk-button-group">',
                '<button id="generate" type="submit" value="Generate" disabled>Generate</button>',
                '<button id="publish" type="submit" value="Publish" disabled>Publish</button>',
                '</div>',
                '<table class="js-rows">',
                '<thead>',
                '<tr>',
                '<th class="" width="">Document status</th>',
                '</tr>',
                '</thead>',
                '<tr>',
                '<td data-heading="Document status">New</td>',
                '<td data-heading="">',
                '<label aria-label="id">',
                '<input id="newRadio" type="radio" name="id" value="22">',
                '</label>',
                '</td>',
                '</tr>',
                '<tr>',
                '<td data-heading="Document status">Generated</td>',
                '<td data-heading="">',
                '<label aria-label="id">',
                '<input id="generatedRadio" type="radio" name="id" value="23">',
                '</label>',
                '</td>',
                '</tr>',
                '</table>',
                '</form>'
            ].join('\n');
            this.body = $('body');
            this.body.append(template);
        });

        describe('when initialised with valid options', function() {

            var _component;
            var options = {
                dataElSelector: '[data-heading="Document status"]',
                dataElValToCheck1: 'New',
                dataElValToCheck2: 'Generated',
                buttonSelector1: '#publish',
                buttonSelector2: '#generate',
                stateAttr: 'disabled',
                stateAttrVal: true
            };

            beforeEach(function() {
                _component = this.component(options);
            });

            it('should set the buttons disabled state when a change event is triggered on the document', function() {
                expect($('#publish').prop('disabled')).to.be(true);
                expect($('#generate').prop('disabled')).to.be(true);
            });

            it('should disable the publish button when a new document is selected', function() {
                $('#newRadio').click();
                _component.onChange();
                expect($('#publish').prop('disabled')).to.be(true);

            });

            it('should disable the generate button a generated document is selected', function() {
                $('#generatedRadio').click();
                _component.onChange();
                expect($('#generate').prop('disabled')).to.be(true);

            });

        }); // when initialised with valid options

    }); // Given a stubbed DOM

});
