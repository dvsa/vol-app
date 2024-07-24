var OLCS = OLCS || {};

/**
 * Split screen
 *
 * Handles the splitScreen js
 */

OLCS.splitScreen = (function(document, $, undefined) {

  'use strict';

  return function init() {

    var url1;
    var url2;
    var orientation;
    var isVisible;
    var canStore;
    var orientationPreferenceIndex = 'OLCS.preferences.splitscreen.orientation';
    var mainFrame = $('#iframe-one');
    var sideFrame = $('#iframe-two');
    var panel = $('.iframe-controls');

    // Splits the hash fragment into the relevant variables, format is
    // #base64encode(url1|url2|orientation|isVisible)
    function splitHashFragment() {
      var parts = window.atob(window.location.hash.substring(1)).split('|');

      url1 = parts[0];
      url2 = parts[1];
      orientation = parts[2] || null;
      isVisible = parts[3] === '0' ? false : true;
    }

    // Checks whether we can access local storage for preferences
    function checkStore() {
      try {
        localStorage.setItem('checkStore', true);
        canStore = true;
      } catch (err) {
        canStore = false;
      }
    }

    function isValidOrientation(orientation) {
      return $.inArray(orientation, ['vertical', 'horizontal', 'closed']) !== -1;
    }

    // Grabs the default orientation,
    //  - checks the hash fragment first
    //  - then checks local storage preferences
    function getDefaultOrientation() {

      var defaultOrientation;

      if (isValidOrientation(orientation)) {
        return orientation;
      }

      defaultOrientation = getOrientationPreference();

      if (!isValidOrientation(defaultOrientation)) {
        defaultOrientation = 'horizontal';
      }

      return defaultOrientation;
    }

    // Grab the orientation preference from local storage if we can
    function getOrientationPreference() {
      if (canStore) {
        return localStorage.getItem(orientationPreferenceIndex);
      }
    }

    // Save our current orientation to the preferences local storage if we can
    function setOrientationPreference(orientation) {
      if (canStore) {
        localStorage.setItem(orientationPreferenceIndex, orientation);
      }
    }

    // Called when the close button is clicked
    function close() {
      // Resize the iframe
      mainFrame.attr('class', 'iframe--full');

      // We need to update the hash to remember that we are closed
      // in case someone refreshes the page
      orientation = 'closed';
      updateHashFragment();

      // We no longer care about the side frame or panel so we can remove them
      sideFrame.remove();
      panel.remove();

      mainFrame.contents().find('form,a').each(function () {
        if (!$(this).attr('target')) {
          $(this).attr('target', '_parent');
        }
      });

      OLCS.preloader.hide();
    }

    // Update the has fragment in the url
    function updateHashFragment() {
      var string = url1 + '|' + url2 + '|' + orientation + '|' + (isVisible ? '1' : '0');
      window.location.hash = '#' + window.btoa(string);
    }

    // @param {string} newOrientation
    function setOrientation(newOrientation) {
      orientation = newOrientation;

      setOrientationPreference(orientation);

      mainFrame.attr('class', 'iframe--' + orientation);
      sideFrame.attr('class', 'iframe--' + orientation);
      panel.attr('class', 'iframe-controls iframe-controls--' + orientation);

      updateHashFragment();
    }

    // Called when we toggle collapsing the split screen
    function toggleVisible() {
      isVisible = !isVisible;
      updateHashFragment();
      panel.toggleClass('collapsed');
      sideFrame.toggleClass('collapsed');
      mainFrame.filter('.iframe--horizontal').toggleClass('full');
    }

    function setUp() {
      splitHashFragment();

      // Start loading the iframes
      mainFrame.attr('src', url1);
      sideFrame.attr('src', url2);

      checkStore();

      if (orientation === 'closed') {
        OLCS.preloader.show();
        mainFrame.attr('class', 'iframe--full');
        panel.remove();
        // In this case, we have to wait for the mainFrame to load before calling close,
        // otherwise it won't set target parent on anything
        $(mainFrame).on('load', function() {
          close();
        });
      } else {
        setOrientation(getDefaultOrientation());
      }

      if (!isVisible) {
        toggleVisible();
      }
    }

    // Listeners
    $(document).on('click', '.iframe-controls--orientation', function toggledOrientation(e) {
      e.preventDefault();
      setOrientation(orientation === 'vertical' ? 'horizontal' : 'vertical');
    });

    $(document).on('click', '.iframe-controls--toggle', function toggledVisibility(e) {
      e.preventDefault();
      toggleVisible();
    });

    $(document).on('click', '.iframe-controls--close', function closeSplitScreen(e) {
      e.preventDefault();
      close();
    });

    setUp();
  };
}(document, window.jQuery));
