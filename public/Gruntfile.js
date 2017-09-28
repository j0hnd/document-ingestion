module.exports = function(grunt) {
    require('jit-grunt')(grunt);

    grunt.initConfig({
        less: {
            development: {
                options: {
                    compress: true,
                    yuicompress: true,
                    optimization: 2
                },
                files: {
                    "css/build/style.css": "less/style.less" // destination file and source file
                }
            }
        },
        concat: {
            dist: {
                src: [
                    'libs/components/jquery/dist/jquery.min.js' ,
                    "src/bootstrap.min.js",
                    'libs/components/underscore/underscore-min.js' ,
                    'libs/components/moment/min/moment.min.js' ,
                    'src/js/helpers.js',
                    'libs/components/dropzone/dist/min/dropzone.min.js'
                ],
                dest: 'src/dist/build.js',
            }
        },
        uglify: {
            dist: {
                files: {
                    'src/dist/build.min.js': ['src/dist/build.js']
                }
            }
        },
        notify: {
            task_name: {
                options: {
                    // Task-specific options go here.
                }
            },
            watch: {
                options: {
                    title: 'Task Complete',  // optional
                    message: 'SASS and Uglify finished running', //required
                }
            } ,
            server: {
                options: {
                    message: 'Server is ready!'
                }
            }
        } ,
        watch: {
            styles: {
                files: ['less/*.less'], // which files to watch
                tasks: ['less'],
                options: {
                    nospawn: true
                }
            } ,
            scripts: {
                files: ['js/*.js'],
                tasks: ['jshint'],
                options: {
                    spawn: true,
                },
            },
        }
    });

    grunt.loadNpmTasks('grunt-notify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.registerTask('default', ['less', 'concat' , 'uglify' , 'notify:watch' , 'notify:server' , 'watch']);
};
