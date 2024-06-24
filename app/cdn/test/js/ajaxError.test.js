/**
 * OLCS.ajaxError
 *
 * grunt test:single --target=ajaxError
 */


describe("OLCS.ajaxError", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.ajaxError;
  });

  it("should be an object", function() {
    expect(this.component).to.be.an("object");
  });

  describe("calling errorHTML", function(){
  	beforeEach(function(){
  		this.errorHTML = this.component.errorHTML("en");
  		this.parser = new DOMParser();
  		this.parsedHTML = this.parser.parseFromString(this.errorHTML, "application/xml");
  	});

  	afterEach(function(){
  		delete this.errorHTML;
  	});

  	it("should return valid HTML that isn't empty", function(){
  		expect(typeof(this.errorHTML)).to.be("string");
  		expect(this.errorHTML.length).to.be.greaterThan(0);
  		var parseErrors = $(this.parsedHTML).find("parsererror");
  		expect(parseErrors.length).to.be(0);
  	});

    it("should contain the english language string", function(){
      var translationString = this.component.translations.en;
      expect(this.errorHTML.indexOf(translationString)).to.be.greaterThan(-1);
    });

  });

  describe("calling errorHTML with welsh language", function(){
    beforeEach(function(){
      this.errorHTML = this.component.errorHTML("cy");
    });

    afterEach(function(){
      delete this.errorHTML;
    });

    it("return value contain the welsh language string", function(){
      var translationString = this.component.translations.cy;
      expect(this.errorHTML.indexOf(translationString)).to.be.greaterThan(-1);
    });

  });

  describe("calling errorHTML with an invalid language", function(){
    beforeEach(function(){
      this.errorHTML = this.component.errorHTML("foobar");
    });

    afterEach(function(){
      delete this.errorHTML;
    });

    it("return value contain the english language string", function(){
      var translationString = this.component.translations.en;
      expect(this.errorHTML.indexOf(translationString)).to.be.greaterThan(-1);
    });

  });

  describe("calling getCookie with a sample cookie string", function(){
    
    it("should retrun the correct language", function(){
      var cookieStringCY = "cookie_seen=1; PHPSESSID=30d2dtrk1pki4mgk1pbgnlool4; langPref=cy; _ga=GA1.4.1569349299.1497964200; _gid=GA1.4.2092157108.1498575475"
      expect(this.component.getCookie(cookieStringCY, "langPref")).to.be("cy");
      var cookieStringEN = "cookie_seen=1; PHPSESSID=30d2dtrk1pki4mgk1pbgnlool4; langPref=en; _ga=GA1.4.1569349299.1497964200; _gid=GA1.4.2092157108.1498575475"
      expect(this.component.getCookie(cookieStringEN, "langPref")).to.be("en");
    });

  });

  describe("given a stubbed DOM", function(){
  	beforeEach(function(){
  		$("body").append("<div class=\"page-header\" id=\"stub\"></div>");
  	});

  	afterEach(function(){
  		$("#stub").remove();
  	});

  	describe("calling showError ", function(){
  		beforeEach(function(){
  			this.component.showError();
  		});

      afterEach(function(){
        $("#stub").html("");
      });

  		it("should attach the warning to the page-header div", function(){
  			expect($(".page-header").find(".notice-container").length).to.be(1);
  		});

      describe("calling removeError when there is an error message", function(){
        beforeEach(function(){
          this.component.removeError();
        });

        it("should remove the error", function(){
          expect($(".page-header").find(".notice-container").length).to.be(0);
        });
      });
  	});

    describe("calling showError more than once", function(){
      beforeEach(function(){
        this.component.showError();
        this.component.showError();
      });
      afterEach(function(){
        $("#stub").html("");
      });

      it("should not attach more than one warning", function(){
        expect($(".page-header").find(".notice-container").length).to.be(1);
      });
    });

    describe("if a modal is visible", function(){
      beforeEach(function(){
        OLCS.modal.show();
        this.component.showError();
      });
      afterEach(function(){
        OLCS.modal.hide()
      });

      it("should add the error message to the modal", function(){
        expect($(".page-header").find(".notice-container").length).to.be(0);
        expect($(".modal").find(".notice-container").length).to.be(1);
      });
    });
  });
});