describe("OLCS.dataTooltip", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.dataTooltip;
  });

  it("should be a function", function() {
    expect(this.component).to.be.a("function");
  });
  
  describe("Given a stubbed DOM with the tooltip data attribute", function() {
    
    beforeEach(function() {
      $("body").append([
        "<div id='stub' data-tooltip='I am a tooltip'>Menu</div>"
      ].join("\n"));
    });

    afterEach(function() {
      $("#stub").remove();
    });
    
    describe("When invoked", function() {
      
      beforeEach(function() {
        this.component();
      });
      
      describe("Test that the tooltip is correctly created", function() {
  
        it("the parent should now have the class 'tooltip-parent'", function() {
          expect($("#stub").hasClass("tooltip-parent")).to.be(true);
        });
  
        it("and the tooltip content should be created", function() {
          expect($("#stub .tooltip").length).to.be.greaterThan(0);
        });
        
      });
    
    });

  });

});