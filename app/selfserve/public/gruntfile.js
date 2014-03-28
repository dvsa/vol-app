module.exports = function(grunt) {

    grunt.initConfig({
        sass: {
            dist: {
                options: {
                    style: 'expanded'
                },
                files: {
                    'stylesheets/main.css': 'stylesheets/main.scss',
                    'stylesheets/styleguide.css': 'stylesheets/styleguide.scss',
                }
            }
        },
        watch: {
            scripts: {
                files: ['stylesheets/*.scss'],
                tasks: ['sass'],
                options: {
                    spawn: false,
                },
            },

        }
    });

    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');

    grunt.registerTask('default', ['watch','sass']);

}