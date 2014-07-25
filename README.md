# Moyo CCK - Translations

## Description

The translations component is one of the main components of the Moyo CCK. It handles the translation status of each CCK
content item as long as it has been configured to use translations.

This component was developed by [Moyo Web Architects](http://moyoweb.nl).

## Requirements
* Joomla 3.X or Joomla 2.5.
* Koowa 0.9 or 1.0 (as yet, Koowa 2 is not supported)
* PHP 5.3.10 or better
* Composer
* Bootstrap 2 or 3

Many Moyo components are depending on this component, so make sure to install it.

## Installation

### Composer

Installation is done through composer. In your `composer.json` file, you should add the following lines to the repositories
section:

```json
{
    "name": "moyo/translations",
    "type": "vcs",
    "url": "https://git.assembla.com/moyo-content.translations.git"
}
```

The require section should contain the following line:

```json
    "moyo/translations": "1.1.*",
```

Afterward, just run `composer update` from the root of your Joomla project.

### jsymlinker

Another option, currently only available for Moyo developers, is by using the jsymlink script from the [Moyo Git
Tools](https://github.com/derjoachim/moyo-git-tools).

## API

### Behaviors - Database

#### Translatable

This behavior handles translation statuses for the items that have been configured as being translatable. Each translation
status is saved in a database table named `#__translations_translations` and contains the table name, row, language,
translation status and originality toggle.

To configure whether an element should be translatable, the following code should be added:

```php

class ComPackageDatabaseTableTablename extends KDatabaseTableDefault
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'behaviors' => array(
                'com://admin/translations.database.behavior.translatable',
            )
        ));

        parent::_initialize($config);
    }
}
```

### Behaviors - Controller

#### Translatable

Checks translation status for the package rows. If a translation does not exist and it is not marked as being the
initial translation, it will try to retrieve the initial translation and will return this one. Additionally, a warning
will be displayed that there is currently no translation available and that the content is shown in its original language.

This is done automatically.

### Template helpers

#### Languages (administrator)

This helper renders each published language as a label and gives it a color according to its status:

* **Blue** This is the language the content item was originally written in.
* **Green** The content has been translated in current language
* **Orange** The content is not translated in current language and is fewer than 14 days old.
* **Red** The content is not translated in current language and more than 14 days old.

#### Language (site)

Makes sure to display a warning when a translation is not available for the current language.

## TODO

 * Assign a *Will-not-translate* status to an element that will not be translated. Background color in the plural view will
 be grey.
 * Generate a legend for colored statuses in backend plural views.