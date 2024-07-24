/**
 * OLCS.searchFilter
 *
 * grunt test:single --target=searchFilter
 */
describe('OLCS.searchFilter', function() {
  
  'use strict';

  beforeEach(function() {
    this.component = OLCS.searchFilter;
  });

  it('should be a function', function() {
    expect(this.component).to.be.a('function');
  });
  
  describe('Given a stubbed DOM', function() {
    
    beforeEach(function() {
      $('body').append([
        '<div id="stub">',
          '<h3 id="title">Lorem Ipsum</h3>',
          '<div id="content">Lorem Ipsum</div>',
        '</div>'
      ].join("\n"));
    });

    afterEach(function() {
      $('#stub').remove();
    });
  
    it('the content should be visible', function() {
      expect($('#content').is(':visible')).to.be(true);
    });
    
    describe('When invoked', function() {
      
      beforeEach(function() {
        this.component({
          parent: '#stub',
          title: '#title',
          content: '#content',
          mobile: false
        });
      });
  
      it('the title should have appropriate aria-expanded attribute', function() {
        expect($('#title').attr('aria-expanded')).to.be('false');
      });
  
      it('the title should have appropriate aria-controls attribute', function() {
        expect($('#title').attr('aria-controls')).to.be('content');
      });
  
      it('the content should have appropriate aria-hidden attribute', function() {
        expect($('#content').attr('aria-hidden')).to.be('true');
      });
  
      it('the content should have appropriate aria-labelledby attribute', function() {
        expect($('#content').attr('aria-labelledby')).to.be('title');
      });
  
      it('the content should be hidden', function() {
        expect($('#content').is(':visible')).to.be(false);
      });
      
      describe('When the title is clicked to open', function() {
      
        beforeEach(function() {
          $('#title').click();
        });
        
        afterEach(function() {
          $('#title').click();
        });
      
        it('the title should have appropriate aria-expanded attribute', function() {
          expect($('#title').attr('aria-expanded')).to.be('true');
        });
  
        it('the content should have appropriate aria-hidden attribute', function() {
          expect($('#content').attr('aria-hidden')).to.be('false');
        });
    
        it('the content should be visible', function() {
          expect($('#content').is(':visible')).to.be(true);
        });
        
      });
      
      describe('When the title has been clicked to close', function() {
      
        it('the title should have appropriate aria-expanded attribute', function() {
          expect($('#title').attr('aria-expanded')).to.be('false');
        });
  
        it('the content should have appropriate aria-hidden attribute', function() {
          expect($('#content').attr('aria-hidden')).to.be('true');
        });
    
        it('the content should be hidden', function() {
          expect($('#content').is(':visible')).to.be(false);
        });
        
      });
    
    });

  });

});