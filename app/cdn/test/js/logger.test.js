/**
 * OLCS.logger
 *
 * grunt test:single --target=logger
 */

describe('OLCS.logger', function() {

  'use strict';

  beforeEach(function() {
    this.component = OLCS.logger;
  });

  describe("when setting a log level", function(){

    describe("level: ERROR", function(){
      beforeEach(function(){
        this.component.setLevel("ERROR")
      });

      it("getLevel should return the correct level", function(){
        expect(this.component.getLevel()).to.be(1);
      });
    });

    describe("level: warn", function(){
      beforeEach(function(){
        this.component.setLevel("WARN")
      });

      it("getLevel should return the correct level", function(){
        expect(this.component.getLevel()).to.be(2);
      });
    });

    describe("level: INFO", function(){
      beforeEach(function(){
        this.component.setLevel("INFO")
      });

      it("getLevel should return the correct level", function(){
        expect(this.component.getLevel()).to.be(3);
      });
    });

    describe("level: DEBUG", function(){
      beforeEach(function(){
        this.component.setLevel("DEBUG")
      });

      it("getLevel should return the correct level", function(){
        expect(this.component.getLevel()).to.be(4);
      });
    });

    describe("level: VERBOSE", function(){
      beforeEach(function(){
        this.component.setLevel("VERBOSE")
      });

      it("getLevel should return the correct level", function(){
        expect(this.component.getLevel()).to.be(5);
      });
    });
    
  });

});


