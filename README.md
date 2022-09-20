Console UI Bundle
============ 

> With great power comes great responsibility.

> 🚧 We are at early development stage, every contribution of every type will be welcome and properly attributed.


## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

## Features

- [x] Execute Single Command
- [x] Accept Input Arguments
- [x] Accept Input Options
- [ ] Copy Command Line to Clipboard
- [ ] Symfony Messenger Support
- [ ] Kill Command execution
- [ ] Flex recipe - Installer - Out of the box usage

## Todos

- [ ] Refactor the socket connection to avoid http1 limit of 6 concurrent connections

#### PHP 

- [ ] Make it public
- [ ] Upload to packagist

#### TypeScript

- [ ] Make it public
- [ ] Extract web-component as NPM package
- [ ] Cover with unit tests



### Dependencies

#### App Build

* symfony/symfony/webpack-encore-bundle: Default

#### Socket Server

* symfony/mercure: Required

#### Queue System

* enqueue/enqueue-bundle: Default
* enqueue/enqueue-fs: Default

## Applications that use Symfony Flex

> We haven't done any Flex recipe yet, look at the next section for the bundle configuration.

Open a command console, enter your project directory and execute:

```console
$ composer require drinksco/console-ui-bundle
```

## Applications that don't use Symfony Flex

### Step 1: Configure dependencies

#### Install Symfony Webpack Encore Bundle

Webpack Encore Bundle allows us to use modern Front-end languages inside our PHP applications.
Follow [the official docs](https://symfony.com/doc/current/frontend/encore/installation.html) to get it up and running.

```bash
composer require symfony/webpack-encore-bundle
```

#### Install Symfony Mercure Component and Hub

Mercure is a high performance socket server, it allows us to get realtime console output in the UI. 
Follow [the official docs](https://symfony.com/doc/current/mercure.html) to get it up and running.

Using flex Mercure will configure automatically for us.

```bash
composer require mercure
```

Mercure requires a dedicated Hub you can use an open source version from [Mercure.Rocks](https://mercure.rocks/docs/hub/install).

Using the binary we can start the Mercure Hub with a commands like follows:

```bash
JWT_KEY='!ChangeThisMercureHubJWTSecretKey!' ADDR='localhost:3001' ALLOW_ANONYMOUS=1 CORS_ALLOWED_ORIGINS=* ./mercure
```

> Why Mercure? It allows us to communicate between background running commands and frontend. We can search another
"more friendly" alternative  as running commands inside an http request, but it will hurt directly the console tool
performance, or it will not be possible to run long processes.

#### Install Forma-Pro Enqueue Bundle

Enqueue Bundle allows us to run commands in its own processes, combined with Mercure Sockets it gives us the real-time 
execution flow.

Follow [the official docs](https://php-enqueue.github.io/bundle/quick_tour/) to get it up and running.

Then install the Filesystem Transport it will do the work :wink

```bash
composer require enqueue/enqueue-bundle enqueue/fs
```

### Step 2: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require --dev drinksco/console-ui-bundle
```

### Step 3: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Drinksco\ConsoleUiBundle\ConsoleUiBundle::class => ['dev' => true],
];
```

### Step 4: Configure Webpack

> Console UI Web component should be updated to npm

Then [enable TypeScript support](https://symfony.com/doc/current/frontend/encore/typescript.html). And last add an
entry for the `console-ui` web component.

While we are not available console-ui component via npm, we need to install and configure it.

```bash
yarn add --dev @hotwired/stimulus @symfony/stimulus-bridge @symfony/webpack-encore core-js electron regenerator-runtime ts-loader typescript webpack-notifier @material/card @material/mwc-button @material/mwc-checkbox @material/mwc-circular-progress @material/mwc-dialog @material/mwc-formfield @material/mwc-icon @material/mwc-list @material/mwc-textfield @material/mwc-top-app-bar-fixed @webcomponents/webcomponentsjs lit material-components-web
```

Then build the Web Components
```bash
yarn encore production
```

```javascript
// webpack.config.js

    .addEntry('component-loader', './node_modules/@webcomponents/webcomponentsjs/webcomponents-loader.js')
    .addEntry('console-ui', './vendor/drinksco/console-ui-bundle/assets/app.js')
```

### Step 5: Run Queue

```bash
 bin/console enqueue:consume --client=console_queue
```

### Step 6: Import Routes

```yaml
# config/routes/console-ui.yaml
when@dev:
    cli:
      resource: '@ConsoleUiBundle/Resources/config/console-ui/routes.yaml'
      prefix: /cli

```

### Step 7: Environment Variables

Until we create the flex recipe, we will need to setup two environment variables.

```
CONSOLE_HOST=http://127.0.0.1:3000
CONSOLE_QUEUE_DSN=file:///path-to-project/var/queue/enqueue?pre_fetch_count=1&polling_interval=100
```

### Step 8: Run Web Server

```bash
php -S 127.0.0.1:3000 -t public
```

### Step 9: Execute Electron App

in the `package.json` file:

```json
...
    "main": "./vendor/drinksco/console-ui-bundle/main.js",
    "scripts": {
        ...
        "console-ui-start": "electron ."
    }
```

```bash
yarn console-ui-start
```
