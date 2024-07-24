describe("OLCS.textareaPlaceholder", function() {
  
  "use strict";

  beforeEach(function() {
    this.component = OLCS.textareaPlaceholder;
  });

  it("should be a function", function() {
    expect(this.component).to.be.a("function");
  });
  
  describe("Given a textarea element with a value equal to the placeholder attribute", function() {
    
    beforeEach(function() {
      $("body").append([
        "<textarea id='stub' placeholder='Lorem Ipsum'>Lorem Ipsum</textarea>"
      ].join("\n"));
    });
    
    afterEach(function() {
      $("#stub").remove();
    });
    
    describe("When invoked using basic options", function() {
      
      beforeEach(function() {
        this.component();
        OLCS.eventEmitter.emit("render");
      });
      
      it("The textarea should now have no value", function() {
          expect($("#stub").val()).to.be('');
      });
      
      it("The textarea should have an appropriate placeholder", function() {
          expect($("#stub").attr('placeholder')).to.be('Lorem Ipsum');
      });
    
    });
      
  });
  
});