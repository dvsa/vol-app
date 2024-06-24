/**
 * 1) Defensively declare our project-level namespace
 */
var OLCS = OLCS || {};

/**
 * 2) Attach our component to our namespace using an
 * IIFE (Immediately Invoked Function Expression).
 *
 * Doing so means that everything within the function
 * has its own scope; this leaves it up to us what we
 * expose as the component's public interface.
 *
 * Use camelCase to name your component, and unless
 * you have a good reason not to, leave the arguments
 * as follows:
 *
 * document - this injects the global document variable locally,
 * meaning we avoid a scope lookup when accessing it within
 * the component
 *
 * $ - the jQuery variable, again scoped locally
 *
 * undefined - an edge case, but this prevents accidental reassignment
 * of what undefined represents and means you can test against it
 * directly
 */
OLCS.myComponent = (function(document, $, undefined) {

  /**
   * 3) Invoking strict mode prevents us from making silly
   * mistakes like accidentally declaring globals and converting
   * some errors into exceptions. It shares the current scope
   * so everything within the component is assured to be
   * evaluated in strict mode
   */
  "use strict";

  /**
   * 4) Declare your component here. This example uses the module pattern
   * to attach public methods to an exports object, but use whatever
   * works best for you
   */
  var exports = {};

  exports.foo = function() {
    //
  };

  exports.bar = function() {
    //
  };

  /**
   * 5) Whatever happens, you must return *something*, otherwise
   * OLCS.myComponent will be undefined which probably isn't what
   * you want.
   */
  return exports;

  /**
   * 6) inject document and jQuery into the component; these are
   * effectively the values we supply for the arguments declared
   * in (2). Note that we *don't* provide an argument for `undefined`,
   * hence why it's... well, undefined...
   */
}(document, window.jQuery));
