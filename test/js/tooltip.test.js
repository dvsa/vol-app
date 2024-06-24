/**
 * OLCS.tooltip.js
 *
 * Whilst the actual tooltip functionality is handled purely 
 * with CSS, this file is required for appropriate ARIA labels
 * 
 * grunt test:single --target=tooltip
 */

describe("OLCS.tooltip", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.tooltip;
  });

  it("should be a function", function() {
    expect(this.component).to.be.a("function");
  });
  
  describe("Given a stubbed tooltip element", function() {
    
    beforeEach(function() {
      $("body").append([
        "<div id='stub' class='tooltip-parent'><span class='tooltip'>Tooltip</span></div>"
      ].join("\n"));
    });

    afterEach(function() {
      $("#stub").remove();
    });
    
    describe("When the tooltip is hovered", function() {
      
      beforeEach(function() {
        this.component({
          parent: '.tooltip-parent'
        });
        $('#stub').trigger('mouseenter');
      });
      
      describe("Test that the 'aria-hidden' attribute is properly set", function() {
  
        it("The parent should have an 'aria-hidden' attribute", function() {
          expect($("#stub").attr("aria-hidden")).to.not.be(null);
        });
       
        it("The 'aria-hidden' attribute should be false", function() {
          expect($("#stub").attr("aria-hidden")).to.be('false');
        });
        
      });
      
      describe("When the tooltip is un-hovered", function() {
        
        beforeEach(function() {
          $('#stub').trigger('mouseleave');
        });
        
        describe("Test that the 'aria-hidden' attribute is properly set", function() {
    
          it("The parent should have an 'aria-hidden' attribute", function() {
            expect($("#stub").attr("aria-hidden")).to.not.be(null);
          });
        
          it("The 'aria-hidden' attribute should be true", function() {
            expect($("#stub").attr("aria-hidden")).to.be('true');
          });
          
        });
      
      });
    
    });

  });

});