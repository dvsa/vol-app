var sass = require("sass");
(function () {
  /**
   * Gruntfile.js
   *
   * OLCS automated front-end build processes to setup the
   * OLCS-Static repo using the Grunt build tool. Ensure to
   * read documentation on the Wiki @ https://wiki.i-env.net
   * note production uses Yarn
   */

  "use strict";

  module.exports = function (grunt) {
    var path = require("path");
    /**
     * Global Configuration
     *
     * General reusable variables and functions for use with
     * all Grunt tasks. You can pass any desired global config
     * to the 'globalConfig' variable.
     */

    // Set any global grunt configuration
    var globalConfig = {};

    // Set development environment to determine asset minification
    var env = grunt.option("env") || "dev";

    // Setup param to access via command line
    var target = grunt.option("target");

    // Set the location for the public images directory
    var pubImages = "public/images";

    // Function to get all scripts for use with a given theme
    var scriptPaths = function (theme) {
      var files = [
        "node_modules/jquery/dist/jquery.min.js",
        "node_modules/chosen-npm/public/chosen.jquery.min.js",
        "assets/_js/vendor/jquery.details.min.js",
        "assets/_js/vendor/URI.min.js",
        "node_modules/@editorjs/editorjs/dist/editorjs.umd.js",
        "node_modules/@editorjs/header/dist/header.umd.js",
        "node_modules/@editorjs/list/dist/list.umd.js",
        "node_modules/@editorjs/paragraph/dist/paragraph.umd.js",
        "assets/_js/components/*.js",
        "assets/_js/" + theme + "/*.js",
        "assets/_js/init/common.js",
        "assets/_js/init/" + theme + ".js",
      ];

      if (theme === "internal") {
        files.push("node_modules/pace-progress/pace.min.js");
      }
      if (theme === "selfserve") {
        files.push("assets/vendor/custom-modernizr.js", "assets/vendor/cookie-manager.js");
      }

      return files;
    };

    // Define the theme stylesheets
    var styles = {
      "public/styles/print.css": "assets/_styles/themes/print.scss",
      "public/styles/selfserve.css": "assets/_styles/themes/build-selfserve.scss",
      "public/styles/internal.css": "assets/_styles/themes/build-internal.scss",
    };

    var localStyles = {
      "public/styles/print.css": "assets/_styles/themes/print.scss",
      "public/styles/selfserve.css": "assets/_styles/themes/selfserve.scss",
      "public/styles/internal.css": "assets/_styles/themes/internal.scss",
    };

    // Define the main JS files for each theme, using the above function
    var scripts = {
      "public/js/internal.js": scriptPaths("internal"),
      "public/js/selfserve.js": scriptPaths("selfserve"),
    };

    /**
     * Grunt Tasks
     *
     * List of all separate Grunt tasks used by OLCS-Static
     *
     * - sass
     * - postcss
     * - copy
     * - clean
     * - browserSync
     * - uglify
     * - jshint
     * - scsslint
     * - notify
     * - watch
     * - karma
     * - localscreenshots
     */

    grunt.initConfig({
      // Set any global configuration
      globalConfig: globalConfig,

      babel: {
        options: {
          sourceMap: true,
          sourceType: "unambiguous",
          presets: ["@babel/preset-env"],
        },
        dist: {
          files: {
            "assets/vendor/cookie-manager.js": "node_modules/@dvsa/cookie-manager/cookie-manager.js",
          },
        },
      },

      /**
       * Sass
       * https://github.com/sindresorhus/grunt-sass
       */
      sass: {
        local: {
          options: {
            outputStyle: "expanded",
            implementation: sass,
            sourceMap: true,
          },
          files: localStyles,
        },
        dev: {
          options: {
            outputStyle: "expanded",
            implementation: sass,
            sourceMap: true,
          },
          files: styles,
        },
        prod: {
          options: {
            outputStyle: "compressed",
            implementation: sass,
            sourceMap: false,
          },
          files: styles,
        },
      },

      /**
       * Post CSS
       * https://github.com/nDmitry/grunt-postcss
       */
      postcss: {
        options: {
          processors: [require("autoprefixer")],
        },
        internal: {
          options: {
            map: {
              inline: false,
              prev: "public/styles/internal.css.map",
            },
          },
          src: "public/styles/internal.css",
        },
        selfserve: {
          options: {
            map: {
              inline: false,
              prev: "public/styles/selfserve.css.map",
            },
          },
          src: "public/styles/selfserve.css",
        },
      },

      /**
       * Copy
       * https://github.com/gruntjs/grunt-contrib-copy
       */
      copy: {
        images: {
          files: [
            {
              expand: true,
              cwd: "assets/_images/",
              src: ["**/*.{png,jpg,gif,svg,ico}"],
              dest: "public/images/",
            },
            {
              expand: true,
              cwd: "node_modules/govuk-frontend/dist/govuk/assets/images/",
              src: ["**/*.{png,jpg,gif,svg,ico}"],
              dest: "public/assets/images/",
            },
            // Start of refresh assets
            // https://github.com/alphagov/govuk-frontend/releases/tag/v5.10.0
            //
            // Copy govuk-frontend refresh assets, preserve /refresh/ prefix in destination to force cache busting
            // when we use the new refreshed assets.
            //
            // Documentation states that:
            // If you copy the font and image files into your application, you’ll need to copy the
            // dist/govuk/assets/rebrand folder to <YOUR-APP>/assets/rebrand. If you use an automated task to copy
            // the files, you may need to update your task to automatically copy our new folder.
            //
            // Future updates when /refresh is removed when it becomes the default, we revert/remove this.
            // Significant time should have passed to force another cache bust.
            //
            // Retain the previous/current assets (non-rebrand) for backwards compatibility for VOL-Internal or Missed Assets on SS.
            {
              expand: true,
              cwd: "node_modules/govuk-frontend/dist/govuk/assets/rebrand/images/",
              src: ["**/*.{png,jpg,gif,svg,ico}"],
              dest: "public/assets/rebrand/images/",
            },
          ],
        },
        fonts: {
          files: [
            {
              expand: true,
              cwd: "node_modules/govuk-frontend/dist/govuk/assets/fonts/",
              src: ["**/*.{woff2,woff,eot}"],
              dest: "public/assets/fonts/",
            },
          ],
        },
        govukJs: {
          files: [
            {
              expand: true,
              cwd: "node_modules/govuk-frontend/dist/govuk/",
              src: ["govuk-frontend.min.js", "govuk-frontend.min.js.map"],
              dest: "public/js/",
            },
          ],
        },
        manifests: {
          files: [
            {
              expand: true,
              cwd: "node_modules/govuk-frontend/dist/govuk/assets/",
              src: ["**/manifest.json"],
              dest: "public/assets/",
            },
          ],
        },
      },

      /**
       * Clean
       * https://github.com/gruntjs/grunt-contrib-clean
       */
      clean: {
        assets: {
          src: "public/assets",
        },
        manifests: {
          src: "public/assets/**/manifest.json",
        },
        styleguide: {
          src: "public/styleguides/**/*.html",
        },
        images: {
          src: [pubImages],
        },
        stylesheets: {
          src: "public/styles",
        },
        scripts: {
          src: "public/js",
        },
      },

      svg_sprite: {
        dist: {
          // Target basics
          expand: true,
          cwd: "assets/_images/svg",
          src: ["**/*.svg"],
          transform: ["svgo"],
          dest: "public/images/svg",
          // Target options
          options: {
            mode: {
              css: {
                // Activate the «css» mode
                dest: "../../../public/styles",
                sprite: "../images/svg/icon-sprite.svg",
                bust: true,
                prefix: ".",
                dimensions: true,
                layout: "vertical",
                render: {
                  scss: {
                    dest: path.resolve() + "/assets/_styles/core/icon-sprite.scss",
                  },
                },
              },
            },
          },
        },
      },

      /**
       * Browser Sync
       * https://github.com/BrowserSync/grunt-browser-sync
       */
      browserSync: {
        bsFiles: {
          src: ["public/**/*.css", "public/**/*.html"],
        },
        options: {
          port: 7001,
          open: false,
          notify: false,
          ghostMode: {
            clicks: true,
            scroll: true,
            links: true,
            forms: true,
          },
          watchTask: true,
          server: {
            baseDir: "./public",
            middleware: function (req, res, next) {
              res.setHeader("Access-Control-Allow-Origin", "*");
              res.setHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS, PUT, PATCH, DELETE");
              res.setHeader("Access-Control-Allow-Headers", "X-Requested-With,content-type");
              res.setHeader("Access-Control-Allow-Credentials", true);
              next();
            },
          },
        },
      },

      /**
       * Uglify
       * https://github.com/gruntjs/grunt-contrib-uglify
       */
      uglify: {
        local: {
          options: {
            sourceMap: true,
            mangle: false,
            compress: false,
            beautify: true,
          },
          files: scripts,
        },
        dev: {
          options: {
            sourceMap: true,
            mangle: false,
            compress: false,
            beautify: true,
          },
          files: scripts,
        },
        prod: {
          options: {
            sourceMap: false,
            compress: {
              pure_funcs: ["OLCS.logger"],
            },
          },
          files: (function () {
            var result = {};
            for (var key in scripts) {
              result[key] = scripts[key].filter(function (file) {
                return !file.includes("pace.min.js") && !file.includes("node_modules/pace-progress");
              });
            }
            return result;
          })(),
        },
      },

      /**
       * JSHint
       * https://github.com/gruntjs/grunt-contrib-jshint
       */
      jshint: {
        options: {
          jshintrc: ".jshintrc",
        },
        static: ["assets/_js/**/*.js", "!assets/_js/**/vendor/*"],
        apps: [
          "../olcs-common/Common/src/Common/assets/js/inline/**/*.js",
          "../olcs-internal/module/*/assets/js/inline/**/*.js",
          "../olcs-selfserve/module/*/assets/js/inline/**/*.js",
        ],
      },

      /**
       * SCSS-Lint
       * https://github.com/ahmednuaman/grunt-scss-lint
       */
      scsslint: {
        allFiles: ["assets/_styles/**/*.scss", "!assets/_styles/vendor/**/*", "!assets/_styles/core/icon-sprite.scss"],
        options: {
          config: ".scss-lint.yml",
        },
      },

      /**
       * Notify
       * https://github.com/dylang/grunt-notify
       */
      notify: {
        options: {
          sucess: false,
        },
      },

      /**
       * Watch
       * https://github.com/gruntjs/grunt-contrib-watch
       */
      watch: {
        options: {
          livereload: true,
          spawn: false,
        },
        styles: {
          files: ["assets/_styles/**/*.scss"],
          tasks: ["sass:dev", "postcss"],
        },
        scripts: {
          files: ["assets/_js/**/*.js"],
          tasks: ["jshint:static", "uglify:dev"],
        },
        images: {
          files: ["assets/_images/**/*.svg"],
          tasks: ["images", "sass:dev", "postcss"],
        },
      },

      /**
       * grunt-localscreenshots
       * https://github.com/danielhusar/grunt-localscreenshots
       *
       * @NOTE: You'll need PhantomJs installed locally to get
       * this task to work
       */
      localscreenshots: {
        options: {
          path: "styleguides/screenshots/" + target,
          type: "png",
          local: {
            path: "public",
            port: 3000,
          },
          viewport: ["600x800", "768x1024", "1200x1024"],
        },
        src: ["public/styleguides/**/*.html"],
      },
    }); // initConfig

    grunt.loadNpmTasks("grunt-contrib-clean");
    grunt.loadNpmTasks("grunt-contrib-copy");
    grunt.loadNpmTasks("grunt-contrib-uglify");
    grunt.loadNpmTasks("grunt-browser-sync");
    grunt.loadNpmTasks("grunt-sass");
    grunt.loadNpmTasks("grunt-svg-sprite");
    grunt.loadNpmTasks("grunt-babel");
    grunt.loadNpmTasks("grunt-contrib-jshint");
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-gh-pages");
    grunt.loadNpmTasks("grunt-notify");
    grunt.loadNpmTasks("grunt-scss-lint");
    grunt.loadNpmTasks("@lodder/grunt-postcss");

    /**
     * Register Grunt Tasks
     *
     * The below tasks are for compiling the app for various
     * scenarios and environments.
     */

    // Default grunt task
    grunt.registerTask("default", "serve");

    // Function to compile the app
    var compile = function (environment) {
      return [
        "pre-clean", // Clean up any previous builds
        "babel",
        "images",
        "sass:" + environment,
        "postcss",
        "uglify:" + environment,
        "copyfonts",
        "copy:govukJs",
        "copy:manifests",
      ];
    };

    grunt.registerTask("pre-clean", [
      "clean:styleguide",
      "clean:assets",
      "clean:manifests",
      "clean:images",
      "clean:stylesheets",
      "clean:scripts",
    ]);

    grunt.registerTask("copyfonts", ["copy:fonts"]);
    // Compile the app using targeted environment
    // $ grunt compile --env=prod
    grunt.registerTask("compile", compile(env));

    // Compile the app for development environment
    grunt.registerTask("compile:local", compile("local"));

    // Compile the app for development environment
    grunt.registerTask("compile:dev", compile("dev"));

    // Compile the app for production environment
    grunt.registerTask("compile:prod", compile("prod"));

    // JS/SCSS Linting
    grunt.registerTask("lint", ["jshint:static", "scsslint"]);

    // Serve the app for a development environment
    grunt.registerTask("serve", ["compile:local", "browserSync", "watch"]);

    grunt.registerTask("images", ["copy:images", "svg_sprite"]);

    /**
     * Define a single Jenkins build task here for any relevant environments
     *
     * Generally these will be simple wrappers around other tasks. The main
     * point is that we only ever want jenkins to have to run *one* Grunt task
     * so we don't have to update each job's configuration just to build some
     * new stuff; instead we just add it to this task and we're done
     */

    grunt.registerTask("build:staging", ["jshint:static", "compile:prod"]);

    grunt.registerTask("build:demo", ["compile:prod"]);

    grunt.registerTask("build:production", ["jshint:static", "compile:prod"]);

    grunt.registerTask("build:container", ["compile:dev"]);
  };
}).call(this);
