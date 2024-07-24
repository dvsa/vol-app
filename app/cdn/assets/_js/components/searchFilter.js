var OLCS = OLCS || {};

/**
 * Search Filter
 *
 * Convert the search filter into an accordion-esque element
 * on smaller screens
 *
 * grunt test:single --target=searchFilter
 */

  OLCS.searchFilter = (function(document, $, undefined) {

  'use strict';

  return function init(custom) {

    var options = $.extend({
      parent  : '.search-filter',
      content : '.form__filter',
      title   : 'h3',
      class   : 'toggled',
      mobile  : '780px'
    }, custom);
      
    var parent  = $(options.parent);
    var title   = parent.find(options.title);
    var content = parent.find(options.content);

    function setup() {
      content.hide().attr('aria-hidden', 'true');
      parent.removeClass(options.class);
      title.attr({
        'aria-expanded' : 'false',
        'aria-controls' : content.attr('id')
      });
      content.attr({
        'aria-hidden' : 'true',
        'aria-labelledby' : title.attr('id')
      });
    }

    function expand() {
      parent.addClass(options.class);
      title.attr('aria-expanded', 'true');      
      content.show().attr('aria-hidden','false');
      title.one('click', collapse);
    }

    function collapse() {
      parent.removeClass(options.class);
      title.attr('aria-expanded', 'false');
      content.hide().attr('aria-hidden', 'true');
      title.one('click', expand);
    }
    
    function revert() {
      parent.removeClass(options.class);
      title.removeAttr('aria-expanded aria-controls');
      content.show().removeAttr('aria-hidden aria-labelledby');
    }

    $(window).on('load resize', function() {
      if (options.mobile && window.matchMedia('(min-width: ' + options.mobile + ')').matches) {
        revert();
      } else {
        setup();
        title.one('click', expand);
      }
    }).load();

  };

}(document, window.jQuery));