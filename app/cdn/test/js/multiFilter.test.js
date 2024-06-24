describe("OLCS.multiFilter", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.multiFilter;
  });

  it("should be defined", function() {
    expect(this.component).to.exist;
  });

  it("should be a function", function() {
    expect(this.component).to.be.a("function");
  });

  describe("Given a stubbed DOM", function() {
    beforeEach(function() {
      $("body").append([
        "<div id=stub>",
          "<select id=from name='foo' multiple=''>",
            "<option value=v1>VAL1</option>",
            "<option value=v2>VAL2</option>",
            "<option value=v3>VAL3</option>",
          "</select>",
          "<select id=to name='bar' multiple=''>",
            "<optgroup label=VAL1>",
              "<option value=s1>SUB1</option>",
              "<option value=s2>SUB2</option>",
              "<option value=s3>SUB3</option>",
            "</optgroup>",
            "<optgroup label=VAL2>",
              "<option value=s4>SUB4</option>",
              "<option value=s5>SUB5</option>",
              "<option value=s6>SUB6</option>",
            "</optgroup>",
            "<optgroup label=VAL3>",
              "<option value=s7>SUB7</option>",
              "<option value=s8>SUB8</option>",
              "<option value=s9>SUB9</option>",
            "</optgroup>",
          "</select>",
        "</div>"
      ].join("\n"));
    });

    afterEach(function() {
      $("#stub").remove();
    });

    describe("When invoked", function() {
      beforeEach(function() {
        this.component({
          from: "#from",
          to: "#to"
        });
      });

      it("removes all the destination's options", function() {
        expect($("#to option").length).to.equal(0);
      });

      describe("When selecting the first from value", function() {
        beforeEach(function() {
          $("#from").val(["v1"]).change();
        });

        it("shows the correct destination optgroup", function() {
          expect($("#to optgroup").length).to.equal(1);
        });

        it("shows the correct destination options", function() {
          expect($("#to optgroup").prop("label")).to.equal("VAL1");
          expect($("#to option").length).to.equal(3);
        });

        describe("When selecting the second item in the destination dropdown", function() {
          beforeEach(function() {
            $("#to").val(["s2"]).change();
          });

          it("selects the second item in the destination dropdown", function() {
            expect($("#to optgroup:first option:eq(1)").is(":selected")).to.equal(true);
          });

          describe("When adding the second from value", function() {
            beforeEach(function() {
              $("#from").val(["v1", "v2"]).change();
            });

            it("shows the correct destination optgroups", function() {
              expect($("#to optgroup").length).to.equal(2);
            });

            it("shows the correct destination options", function() {
              expect($("#to option").length).to.equal(6);
            });

            it("keeps the second item in the destination selected", function() {
              expect($("#to optgroup:first option:eq(1)").is(":selected")).to.equal(true);
            });
          });
        });
      });
    });
  });
});
