/**
 * OLCS.url
 *
 * grunt test:single --target=url
 */

describe('OLCS.url', function() {

  'use strict';

  beforeEach(function() {
    this.component = OLCS.url;
  });

  it('should be defined', function() {
    expect(this.component).to.exist;
  });

  it('should expose the correct public interface', function() {
    expect(this.component.isSame).to.be.a('function');
    expect(this.component.isCurrentPage).to.be.a('function');
    expect(this.component.load).to.be.a('function');
  });

  describe('isSame', function() {

    it('should return true when given matching paths', function() {
      expect(this.component.isSame('/foo/bar/', '/foo/bar/')).to.be(true);
    });

    it('should return true when given matching paths with inconsistent trailing slashes', function() {
      expect(this.component.isSame('/foo/bar/', '/foo/bar')).to.be(true);
      expect(this.component.isSame('/foo/bar', '/foo/bar/')).to.be(true);
    });

    it('should return false when given non-matching paths', function() {
      expect(this.component.isSame('/foo/bar/', '/foo/bar/baz/')).to.be(false);
    });

    it('should return false when given matching paths but one of them with url parameters', function() {
      expect(this.component.isSame('/foo/bar/', '/foo/bar/?parameter=foo')).to.be(false);
    });

    it('should return false when given matching paths but with different url parameters', function() {
      expect(this.component.isSame('/foo/bar/?random=bar', '/foo/bar/?parameter=foo')).to.be(false);
    });

    it('should return true when given matching paths with same url parameters', function() {
      expect(this.component.isSame('/foo/bar/?random=bar', '/foo/bar/?random=bar')).to.be(true);
    });

    it('should return true when given matching paths with same url parameters but only one with trailing slash', function() {
      expect(this.component.isSame('/foo/bar/?random=bar', '/foo/bar?random=bar')).to.be(true);
    });

  }); // isSame

  describe('isCurrentPage', function() {

    it('should return true when given matching paths', function() {
      expect(this.component.isCurrentPage(window.location.pathname)).to.be(true);
    });

    it('should return true when given matching paths but with fragments', function() {
      expect(this.component.isCurrentPage(window.location.pathname+'#index')).to.be(true);
    });

    it('should return true when given matching full url', function() {
      expect(this.component.isCurrentPage(window.location.href)).to.be(true);
    });

    it('should return false when given non-matching paths', function() {
      expect(this.component.isCurrentPage('/foo/bar/')).to.be(false);
    });

    it('should return false when given matching paths but with url parameter', function() {
      expect(this.component.isCurrentPage(window.location.pathname+'?foo=bar')).to.be(false);
    });

  }); // isCurrentPage

  describe('load', function() {

    //@TODO why does this return undefined?
    it.skip('should return true when given matching paths', function() {
      expect(this.component.load(window.location.pathname)).to.be(true);
    });

  }); // load

});
