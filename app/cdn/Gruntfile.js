var sass = require('sass');
(function() {

    /**
     * Gruntfile.js
     *
     * OLCS automated front-end build processes to setup the
     * OLCS-Static repo using the Grunt build tool. Ensure to
     * read documentation on the Wiki @ https://wiki.i-env.net
     * note production uses Yarn
     */

    'use strict';

    module.exports = function(grunt) {


        var path = require('path');
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
        var env = grunt.option('env') || 'dev';

        // Setup param to access via command line
        var target = grunt.option('target');

        // Set the location for the public images directory
        var pubImages = 'public/images';

        // Function to get all scripts for use with a given theme
        var scriptPaths = function(theme) {
            var files = [
                'node_modules/jquery/dist/jquery.min.js',
                'node_modules/chosen-npm/public/chosen.jquery.min.js',
                'assets/_js/vendor/jquery.details.min.js',
                'assets/_js/vendor/URI.min.js',
                'assets/_js/components/*.js',
                'assets/_js/' + theme + '/*.js',
                'assets/_js/init/common.js',
                'assets/_js/init/' + theme + '.js',
            ];
            if (theme === 'internal') {
                files.push(
                    'node_modules/pace-progress/pace.min.js'
                );
            };
            if(theme === 'selfserve'){
                files.push(
                    'assets/vendor/custom-modernizr.js',
                    'assets/vendor/cookie-manager.js'
                );
            }
            return files;
        };

        // Define the theme stylesheets
        var styles = {
            'public/styles/print.css': 'assets/_styles/themes/print.scss',
            'public/styles/selfserve.css': 'assets/_styles/themes/build-selfserve.scss',
            'public/styles/internal.css': 'assets/_styles/themes/build-internal.scss'
        };

        var localStyles = {
            'public/styles/print.css': 'assets/_styles/themes/print.scss',
            'public/styles/selfserve.css': 'assets/_styles/themes/selfserve.scss',
            'public/styles/internal.css': 'assets/_styles/themes/internal.scss'
        };

        // Define the main JS files for each theme, using the above function
        var scripts = {
            'public/js/internal.js': scriptPaths('internal'),
            'public/js/selfserve.js': scriptPaths('selfserve')
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
         * - assemble
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
                    sourceType: 'unambiguous',
                    presets: ['@babel/preset-env']
                },
                dist: {
                    files: {
                        'assets/vendor/cookie-manager.js': 'node_modules/@dvsa/cookie-manager/cookie-manager.js'
                    }
                }
            },
            /**
             * Sass
             * https://github.com/sindresorhus/grunt-sass
             */
            sass: {
                local: {
                    options: {
                        outputStyle: 'expanded',
		            	implementation: sass,
                        sourceMap: true
                    },
                    files: localStyles
                },
                dev: {
                    options: {
                        outputStyle: 'expanded',
                        implementation: sass,
                        sourceMap: true
                    },
                    files: styles
                },
                prod: {
                    options: {
                        outputStyle: 'compressed',
                        implementation: sass,
                        sourceMap: false
                    },
                    files: styles
                }
            },

            /**
             * Post CSS
             * https://github.com/nDmitry/grunt-postcss
             */
            postcss: {
                options: {
                    processors: [
                        require('autoprefixer')
                    ]
                },
                internal: {
                    options: {
                        map: {
                            inline: false,
                            prev: 'public/styles/internal.css.map',
                        }
                    },
                        src: 'public/styles/internal.css'

                },
                selfserve: {
                    options: {
                        map: {
                            inline: false,
                            prev: 'public/styles/selfserve.css.map',
                        }
                    },
                        src: 'public/styles/selfserve.css'
                }
            },

            /**
             * Copy
             * https://github.com/gruntjs/grunt-contrib-copy
             */
            copy: {
                prototype: {
                    files: [{
                        expand: true,
                        cwd: 'public/styleguides/selfserve/' + target + '/',
                        src: ['**/*.html'],
                        dest: '../prototypes/' + target + '/'
                    }, {
                        expand: true,
                        cwd: 'public/js/',
                        src: ['selfserve.js', target + '.js'],
                        dest: '../prototypes/' + target + '/js/'
                    }, {
                        expand: true,
                        cwd: 'public/styles/',
                        src: ['selfserve.css'],
                        dest: '../prototypes/' + target + '/styles/'
                    }, {
                        cwd: 'public/images/',
                        src: ['**/*.{png,jpg,gif,svg,ico}'],
                        dest: '../prototypes/' + target + '/images/'
                    }, {
                        cwd: 'public/fonts/',
                        src: ['**/*'],
                        dest: '../prototypes/' + target + '/fonts/'
                    }]
                },
                images: {
                    files: [{
                        expand: true,
                        cwd: 'assets/_images/',
                        src: ['**/*.{png,jpg,gif,svg,ico}'],
                        dest: 'public/images/'
                    }, {
                        expand: true,
                        cwd: 'node_modules/govuk-frontend/govuk/assets/images/',
                        src: ['**/*.{png,jpg,gif,svg,ico}'],
                        dest: 'public/assets/images/'
                    }]
                },
                fonts:{
                    files: [{
                        expand: true,
                        cwd: 'node_modules/govuk-frontend/govuk/assets/fonts/',
                        src: ['**/*.{woff2,woff,eot}'],
                        dest: 'public/assets/fonts/'
                    }]
                },
                govukJs: {
                    files: [{
                        expand: true,
                        cwd: 'node_modules/govuk-frontend/dist/govuk/',
                        src: ['govuk-frontend.min.js'],
                        dest: 'public/js/'
                    }]
                }
            },



            /**
             * Clean
             * https://github.com/gruntjs/grunt-contrib-clean
             */
            clean: {
                styleguide: {
                    src: 'public/styleguides/**/*.html'
                },
                prototype: {
                    options: {
                        force: true
                    },
                    src: [
                        '../prototypes/<%= globalConfig.prototypeName %>/**/*.html',
                        '../prototypes/<%= globalConfig.prototypeName %>/**/*.css',
                        '../prototypes/<%= globalConfig.prototypeName %>/**/*.js',
                        '../prototypes/<%= globalConfig.prototypeName %>/**/*.png'
                    ]
                },
                images: {
                    src: pubImages
                }
            },

            svg_sprite: {
                dist: {
                    // Target basics
                    expand: true,
                    cwd: 'assets/_images/svg',
                    src: ['**/*.svg'],
                    transform: ['svgo'],
                    dest: 'public/images/svg',
                    // Target options
                    options: {
                        mode: {
                            css: { // Activate the «css» mode
                                'dest': '../../../public/styles',
                                'sprite': '../images/svg/icon-sprite.svg',
                                'bust': true,
                                'prefix': '.',
                                'dimensions': true,
                                'layout': 'vertical',
                                'render': {
                                  'scss': {
                                      'dest': path.resolve() + '/assets/_styles/core/icon-sprite.scss'
                                  }
                              },
                            }
                        }
                    }
                }
            },

            /**
             * Assemble
             * https://github.com/assemble/grunt-assemble
             */
            assemble: {
                options: {
                    helpers: ['handlebars-helper-repeat']
                },
                internal: {
                    options: {
                        assets: '../../',
                        layout: 'base.hbs',
                        layoutdir: 'styleguides/internal/layouts/',
                        partials: [
                            'styleguides/partials/*.hbs',
                            'styleguides/internal/partials/*.hbs'
                        ]
                    },
                    cwd: 'styleguides/internal/pages',
                    dest: 'public/styleguides/internal',
                    expand: true,
                    src: '**/*.hbs'
                },
                selfserve: {
                    options: {
                        assets: '../../',
                        layout: 'base.hbs',
                        layoutdir: 'styleguides/selfserve/layouts/',
                        partials: [
                            'styleguides/partials/*.hbs',
                            'styleguides/selfserve/partials/*.hbs'
                        ]
                    },
                    cwd: 'styleguides/selfserve/pages',
                    dest: 'public/styleguides/selfserve',
                    expand: true,
                    src: '**/*.hbs'
                }
            },

            /**
             * Browser Sync
             * https://github.com/BrowserSync/grunt-browser-sync
             */
            browserSync: {
                bsFiles: {
                    src: ['public/**/*.css', 'public/**/*.html']
                },
                options: {
                    port: 7001,
                    open: false,
                    notify: false,
                    ghostMode: {
                        clicks: true,
                        scroll: true,
                        links: true,
                        forms: true
                    },
                    watchTask: true,
                    server: {
                        baseDir: './public',
                        middleware: function (req, res, next) {
                            res.setHeader('Access-Control-Allow-Origin', '*');
                            res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH, DELETE');
                            res.setHeader('Access-Control-Allow-Headers', 'X-Requested-With,content-type');
                            res.setHeader('Access-Control-Allow-Credentials', true);
                            next();
                        }
                    }
                }
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
                        beautify: true
                    },
                    files: scripts
                },
                dev: {
                    options: {
                        sourceMap: true,
                        mangle: false,
                        compress: false,
                        beautify: true
                    },
                    files: scripts
                },
                prod: {
                    options: {
                        sourceMap: false,
                        compress: {
                            pure_funcs: ['OLCS.logger']
                        }
                    },
                    files: scripts
                }
            },

            /**
             * JSHint
             * https://github.com/gruntjs/grunt-contrib-jshint
             */
            jshint: {
                options: {
                    jshintrc: '.jshintrc'
                },
                'static': ['assets/_js/**/*.js', '!assets/_js/**/vendor/*'],
                apps: [
                    '../olcs-common/Common/src/Common/assets/js/inline/**/*.js',
                    '../olcs-internal/module/*/assets/js/inline/**/*.js',
                    '../olcs-selfserve/module/*/assets/js/inline/**/*.js'
                ]
            },

            /**
             * SCSS-Lint
             * https://github.com/ahmednuaman/grunt-scss-lint
             */
            scsslint: {
                allFiles: [
                    'assets/_styles/**/*.scss',
                    '!assets/_styles/vendor/**/*',
                    '!assets/_styles/core/icon-sprite.scss'
                ],
                options: {
                    config: '.scss-lint.yml'
                }
            },

            /**
             * Notify
             * https://github.com/dylang/grunt-notify
             */
            notify: {
                options: {
                    sucess: false
                }
            },

            /**
             * Watch
             * https://github.com/gruntjs/grunt-contrib-watch
             */
            watch: {
                options: {
                    livereload: true,
                    spawn: false
                },
                styles: {
                    files: ['assets/_styles/**/*.scss'],
                    tasks: ['sass:dev', 'postcss']
                },
                hbs: {
                    files: ['styleguides/**/*.hbs'],
                    tasks: ['assemble']
                },
                scripts: {
                    files: ['assets/_js/**/*.js'],
                    tasks: ['jshint:static','uglify:dev']
                },
                images: {
                    files: ['assets/_images/**/*.svg'],
                    tasks: ['images', 'sass:dev', 'postcss']
                }
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
                    path: 'styleguides/screenshots/' + target,
                    type: 'png',
                    local: {
                        path: 'public',
                        port: 3000
                    },
                    viewport: [
                        '600x800', '768x1024', '1200x1024'
                    ],
                },
                src: ['public/styleguides/**/*.html']
            },

        }); // initConfig

        /**
         * Load all NPM tasks automatically using 'matchdep'
         */
        require('matchdep').filterAll([
            'grunt-*', '!grunt-cli', 'assemble', '@lodder/grunt-postcss'
        ]).forEach(grunt.loadNpmTasks);

        /**
         * Register Grunt Tasks
         *
         * The below tasks are for compiling the app for various
         * scenarios and environments.
         */

        // Default grunt task
        grunt.registerTask('default', 'serve');

        // Function to compile the app
        var compile = function(environment) {
            return [
                'babel',
                'images',
                'sass:' + environment,
                'postcss',
                'uglify:' + environment,
                'copyfonts',
                'copy:govukJs'
            ];
        };

        grunt.registerTask('copyfonts',
            ['copy:fonts']
        );
        // Compile the app using targeted environment
        // $ grunt compile --env=prod
        grunt.registerTask('compile',
            compile(env)
        );

        // Compile the app for development environment
        grunt.registerTask('compile:local',
            compile('local')
        );

        // Compile the app for development environment
        grunt.registerTask('compile:dev',
            compile('dev')
        );

        // Compile the app for production environment
        grunt.registerTask('compile:prod',
            compile('prod')
        );

        // JS/SCSS Linting
        grunt.registerTask('lint', [
            'jshint:static',
            'scsslint'
        ]);

        // Serve the app for a development environment
        grunt.registerTask('serve', [
            'compile:local',
            'browserSync',
            'watch'
        ]);

        grunt.registerTask('images', [
            'clean:images',
            'copy:images',
            'svg_sprite'
        ]);

        // Create a prototype
        // $ grunt prototype --target=prototypeName
        grunt.registerTask('prototype', [
            'clean:prototype:' + target,
            'copy:prototype:' + target
        ]);

        /**
         * Define a single Jenkins build task here for any relevant environments
         *
         * Generally these will be simple wrappers around other tasks. The main
         * point is that we only ever want jenkins to have to run *one* Grunt task
         * so we don't have to update each job's configuration just to build some
         * new stuff; instead we just add it to this task and we're done
         */

        grunt.registerTask('build:staging', [
            'jshint:static', 'compile:prod'
        ]);

        grunt.registerTask('build:demo', [
            'compile:prod'
        ]);

        grunt.registerTask('build:production', [
            'jshint:static', 'compile:prod'
        ]);

        grunt.registerTask('build:container',[
            'compile:dev'
        ]);
    };

}).call(this);
