describe("OLCS.addAnother", function() {

  "use strict";

  beforeEach(function() {
    this.component = OLCS.addAnother;
  });

  describe("given a stubbed DOM", function() {

    beforeEach(function() {
      $("body").append([
        "<div class='add-another' id=stub>",
          "<fieldset data-group='data[fieldName][0]'>",
            "<div class=field>",
              "<input type=text name='[fieldName][0]name.name' id='[fieldName][0]name.id' value='something'>",
              "<input type=text name='[fieldName][0]id.name' id='[fieldName][0]id.id' value='something else'>",
            "</div>",
          "</fieldset>",
          "<p class=hint><button type='submit' value='Add another'>Add another</button></p>",
        "</div>"
      ].join("\n"));
    });

    afterEach(function() {
      $("#stub").remove();
    });

    describe("and the component is invoked", function() {

      beforeEach(function() {
        this.component();
        OLCS.eventEmitter.emit('render');
      });

      describe("when the user clicks the 'Add another' button", function() {
        beforeEach(function() {
          $(".add-another-trigger").trigger("click");
        });

        it("it creates a new field", function() {
          expect($('fieldset').length).to.equal(2);
        });

        it("should correctly increment the fieldset data attribute", function(){
          var newField = $('#stub fieldset')[1];
          expect(newField.dataset.group).to.equal('data[fieldName][1]');
        });

        it("should correctly incremented the first input's values", function() {
          var newField = $('#stub fieldset')[1];
          expect($(newField).find('input')[0].getAttribute('name')).to.equal('[fieldName][1]name.name');
          expect($(newField).find('input')[0].id).to.equal('[fieldName][1]name.id');
          expect($(newField).find('input')[0].value).to.equal('');
        });

        it("should correctly incremented the second input's values", function() {
          var newField = $('#stub fieldset')[1];
          expect($(newField).find('input')[1].getAttribute('name')).to.equal('[fieldName][1]id.name');
          expect($(newField).find('input')[1].id).to.equal('[fieldName][1]id.id');
          expect($(newField).find('input')[1].value).to.equal('');
        });
      });

    });
  });
});
