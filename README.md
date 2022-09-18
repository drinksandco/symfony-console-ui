Console UI Bundle
============ 

> With great power comes great responsibility.

> 🚧 We are at early development stage, every contribution of every type will be welcome and properly attributed.


## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Dependencies

* symfony/mercure
* symfony/symfony/webpack-encore-bundle
* enqueue/enqueue-bundle
* enqueue/enqueue-fs

## Applications that use Symfony Flex

> We haven't done any Flex recipe yet, look at the next section for the bundle configuration.

Open a command console, enter your project directory and execute:

```console
$ composer require drinksco/console-ui-bundle
```

## Applications that don't use Symfony Flex

### Step 1: Configure dependencies

#### Install Symfony Mercure Component and Hub

Mercure is a high performance socket server, it allows us to get realtime console output in the UI. 
Follow [the official docs](https://symfony.com/doc/current/mercure.html) to get it up and running.

#### Install Symfony Webpack Encore Bundle

Webpack Encore Bundle allows us to use moder Front-end languages inside our PHP applications.
Follow [the official docs](https://symfony.com/doc/current/frontend/encore/installation.html) to get it up and running.

Then [enable TypeScript support](https://symfony.com/doc/current/frontend/encore/typescript.html). And last add an 
entry for the `console-ui` web component. 

```javascript
// webpack.config.js

    .addEntry('component-loader', './node_modules/@webcomponents/webcomponentsjs/webcomponents-loader.js')
    .addEntry('console-ui', './vendor/drinksco/console-ui-bundle/assets/app.js')
```

#### Install Forma-Pro Enqueue Bundle

Enqueue Bundle allows us to run commands in its own processes, combined with Mercure Sockets it gives us the real-time 
execution flow.

Follow [the official docs](https://php-enqueue.github.io/bundle/quick_tour/) to get it up and running.

Then install at least one transport



### Step 2: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require drinksco/console-ui-bundle
```

### Step 3: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Drinksco\ConsoleUiBundle\ConsoleUiBundle::class => ['all' => true],
];
```
