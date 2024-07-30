describe("OLCS.preloader", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.preloader;
  });

  it("should be defined", function() {
    expect(this.component).to.exist;
  });

  it("should expose the correct public interface", function() {
    expect(this.component.show).to.be.a("function");
    expect(this.component.hide).to.be.a("function");
  });

  describe("Given a stubbed empty DOM", function() {
    afterEach(function() {
      $("body").html("");
    });

    describe("when the 'show' method is invoked with a 'modal' option", function() {
      beforeEach(function() {
        this.component.show("modal");
      });

      it("it shows the expetced preloader", function() {
        expect($(".preloader-overlay--modal").is(":visible")).to.be(true);
        expect($(".preloader-icon--modal").is(":visible")).to.be(true)
      });
    });
  });

  describe("Given a stubbed DOM with a '.table__wrapper'", function() {
    beforeEach(function() {
      $("body").append([
        "<div id=stub>",
          "<div class=table__wrapper></div>",
        "</div>"
      ].join("\n"));
    });

    afterEach(function() {
      $("#stub").remove();
    });

    describe("when the 'show' method is invoked with a 'table' option", function() {
      beforeEach(function() {
        this.component.show("table");
      });

      it("it shows the expected preloader", function() {
        expect($(".preloader-overlay--table").is(":visible")).to.be(true);
        expect($(".preloader-icon--table").is(":visible")).to.be(true);
      });
    });
  });


  describe("Given a stubbed DOM containing an element with the classes 'js-find' and 'js-active'", function() {
    beforeEach(function() {
      $("body").append([
        "<div id=stub>",
          "<div class=js-active></div>",
        "</div>"
      ].join("\n"));
    });

    afterEach(function() {
      $("#stub").remove();
    });

    describe("when the 'show' method is invoked with an 'inline' option", function() {
      beforeEach(function() {
        this.component.show("inline");
      });

      it("it shows the expected preloader", function() {
        expect($(".preloader-icon--inline").is(":visible")).to.be(true);
      });
    });
  });


  describe("Given a stubbed DOM with an existing preloader showing", function() {
    beforeEach(function() {
      $("body").append([
        "<div id=stub>",
          "<div class=preloader-overlay--table></div>",
          "<div class=preloader-icon--table></div>",
        "</div>"
      ].join("\n"));
    });

    afterEach(function() {
      $("#stub").remove();
    });

    describe("when the 'show' method is invoked", function() {
      beforeEach(function() {
        this.component.show("modal");
      });

      it("it doesn't show another preloader", function() {
        expect($(".preloader-overlay--modal").is(":visible")).to.be(false);
        expect($(".preloader-icon--modal").is(":visible")).to.be(false);
      });
    });

    describe("when the 'hide' method is invoked", function() {
      beforeEach(function() {
        this.component.hide();
      });

      it("it hides the preloader", function() {
        expect($(".preloader-overlay--table").is(":visible")).to.be(false);
        expect($(".preloader-icon--table").is(":visible")).to.be(false);
      });
    });

  });

});
