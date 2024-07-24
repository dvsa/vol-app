#OLCS Static
### Synopsis
This repo contains the styleguides, prototypes and static assets for both the OLCS internal application and external service.

To view the compiled end result, visit [http://olcs.github.io/olcs-static/](http://olcs.github.io/olcs-static/).

### Change History 
Updated for the OLCS Private Beta. For a detailed change log, see the file named [CHANGELOG.md](./CHANGELOG.md). 

### Known Issues 
There are no known issues with the OLCS Static HTML.

### Contributors 
If you would like to send a bug report or contact us regarding any code-related queries please create an issue within the GitHub project. Valid reports and queries will receive responses within 60 days.

### License 
Copyright (c) 2016 HM Government (Driver and Vehicle Standards Agency) 
Free software published under an MIT License - please see the file named [LICENSE.txt](./LICENSE.txt). 

### Acknowledgements 
The following people created OLCS Static 
* Edmund Reed
* Sam Quayle
* Nick Payne

### Requirements

* [Node.js](https://nodejs.org/en/) v6.9.2

### Installation

##### Clone the repo:

```
git clone https://github.com/OLCS/olcs-static.git
```

##### Install node modules:

```
npm install
```


### Usage

To view the compiled assets as well as continuously compile the assets as files are changed, you can run `
npm run start` to compile the assets and styleguides, run the `watch` task, and set up a local server.

Access the compiled styleguides: 

* [http://localhost:7001/styleguides/selfserve/](http://localhost:7001/styleguides/selfserve/)
* [http://localhost:7001/styleguides/selfserve/](http://localhost:7001/styleguides/internal/)

### Developing

#### JavaScript

All JavaScript files are located within the `assets/_js` directory. This directory is further split up into the following directories:

* [components](./tree/develop/assets/_js/components) (custom JS components)
* [init](./tree/develop/assets/_js/init) (initialise custom JS components)
* [vendor](./tree/develop/assets/_js/vendor) (third party JS)

#### Sass/CSS

All custom CSS is compiled from source *Scss* files which can be found in the `assets/_styles` directory. This directory is further split up into the following directories:

* [components](./tree/develop/assets/_styles/components) (custom Sass components)
* [core](./tree/develop/assets/_styles/core) (core Sass components)
* [vendor](./tree/develop/assets/_styles/vendor) (thid party styles)
* [views](./tree/develop/assets/_styles/views) (styles for specific views)

Desired partials are then imported into the appropriate theme to be processed. 

* [internal](./blob/develop/assets/_styles/themes/internal.scss)
* [selfserve](./blob/develop/assets/_styles/themes/selfserve.scss)

### Build tasks

OLCS uses NPM scripts and Grunt as the front end build tools, with all configuration being contained within `Gruntfile.js` & scripts in package.json. There are several pre-defined tasks which can be executed:

```
$ npm run start
```
This is the main task used for development. It will compile all the assets and start a browsersync server & watch task to monitor and compile assets on the fly. 
```
$ npm run build:staging
```
This is the task that Jenkins will run to build the static assets. It will lint javascript and run unit tests, compile CSS and JS and compile SVG icons/PNG fallback images. 

#### Unit testing

You can run all unit tests by calling the grunt task:

````$ grunt test````

Individual unit tests can be called with:

````$ grunt test:single --taget=compnentName````

e.g.  `grunt test:single --target=ajax` will only run ajax.test.js.