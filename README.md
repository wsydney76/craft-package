# Package

Releases all drafts/disabled entries assigned to a package in one go.

![Screenshot](/images/screenshot1.jpg)

## Requirements

This plugin requires Craft CMS 4.3.5 or later and the [contentoverview plugin](https://github.com/wsydney76/craft-contentoverview)..

## Installation

Run `composer require wsydney76/craft-package` (coming soon...)

Run `craft plugin/install package` or install from settings/plugin page in the Control Panel.

## Further steps

* Edit the `config/contentoverview/package.php`  file (unless reconfigured) and add the sections handled by this plugin.

```php
$co->createSection(PackageSection::class)
    ->section(['news', 'page'])
```

* Edit the `config/package.php` file and set the image field handle(s) for the sections handled by this plugin.
See [docs](https://wsydney76.github.io/craft-contentoverview/config/page-config.html#multi-section-setup) if multiple sections with different fields are used.

```php
<?php

return [
    'imageField' => 'featuredImage'
];
```

* Add the `Package` field to all sections handled by this plugin.

## Usage

* Create a `Package` entry.
* Assign drafts/entries to this package via the `Package` field.
* Maintain your package via the package entries.

![Screenshot](/images/package.jpg)

* or via the `Maintain Package` colum in the entries index.

![Screenshot](/images/elementindex.jpg)  

You can also add a section to your pages setup of the ContentOverview plugin.

```php

// config/contentoverview/pages.php

...
 $co->createPage('packages')
        ->label('Packages'),
...

// config/contentoverview/packages.php

<?php

use wsydney76\contentoverview\Plugin;
use wsydney76\package\models\PackagesSection;


$co = Plugin::getInstance()->contentoverview;

return [
    'tabs' => [
        $co->createTab('Create', [
            $co->createColumn(12, [
                $co->createSection(PackagesSection::class)
                    ->imageField('featuredImage')
                    ->size('medium')
            ])
        ])
    ]
];
```
