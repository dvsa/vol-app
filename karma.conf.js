module.exports = function(config) {
  
  /**
   * Karma.Configuration
   *
   * Configuration file for the Karma test runner, imported by
   * Gruntifle.js and used by Grunt-Karma
   */

  "use strict";

  config.set({
    
    frameworks: ["mocha", "expect","sinon"],
    
    // These files are re-defined in Gruntfile.js
    files: [
      "assets/_js/vendor/jquery.1.12.4.js",
      "assets/_js/vendor/**/*.js",
      "assets/_js/components/**/*.js",
      "test/js/setup.js",
      "test/js/**/*.test.js"
    ],
    
    exclude: [],
    
    preprocessors: {
      "assets/_js/components/*.js": ["coverage"],
      "assets/_js/internal/*.js": ["coverage"],
      "assets/_js/selfserve/*.js": ["coverage"]
    },

    client: {
      captureConsole: true
    },

    reporters: ["dots", "coverage", "junit"],
    port: 9876,
    colors: true,
    logLevel: config.LOG_INFO,
    autoWatch: true,
    captureTimeout: 60000,

    coverageReporter: {
      dir: "public/unit-testing",
      subdir: '.',
      reporters: [
        {type: "html"},
        {type: "lcov"},
        {type: "cobertura"}
      ]
    },
    
    customLaunchers: {
      'PhantomJS_Desktop': {
        base: 'PhantomJS',
          options: {
            viewportSize: {
              width: 1228,
              height: 1000
          }
        }
      }
    },

    junitReporter: {
      outputFile: "test/js/reports/results.xml"
    }
    
  });
  
};