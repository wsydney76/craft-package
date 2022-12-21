# Package

Releases all drafts/disabled entries assigned to a package in one go.

![Screenshot](/images/screenshot1.jpg)

## Requirements

This plugin requires Craft CMS 4.3.5 or later and the [contentoverview plugin](https://github.com/wsydney76/craft-contentoverview)..

## Installation

Run `composer require wsydney76/craft-package` (coming soon...)

Run `craft plugin/install package` or install from settings/plugin page in the Control Panel.

This will 

* create a section `Package` (handle `paPackage`).
* create a field group `Package`.
* create a field `Package` (handle `paPackage`).
* create a field `Maintain Package` (handle `paMaintainPackage`)  and assigns it to the `Package` section.
* create a `config/package.php` plugin setting file.
* create a `config/contentoverview/package.php` page config file. 

Check logs if the installation fails.

## Further steps

* Edit the `config/package.php` file  
* * set the image field handle(s) for the sections handled by this plugin.
See [docs](https://wsydney76.github.io/craft-contentoverview/config/page-config.html#multi-section-setup) if multiple sections with different fields are used.
* * edit the sections that you want to be part of a package. 

```php
<?php

return [
    'imageField' => 'featuredImage',
    'sections' => [
        'paPackage' => ['news', 'page'],
    ]
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

## Use existing sections as a package

Beneath the dedicated `Package` section, the package functionality can be added to any existing projects.

For example you may wish to link `Screening` entries to a `Film` in order to release all of them in one go together with the film.


* Add the `Maintain Package` field to the films' field layout.
* Create a new entries field which allow only to link to film entries.
* Attach it to the screenings field layout.

Update your `config/package.php` file:

```php{3,7}
 'sections' => [
    'paPackage' => ['news', 'page'],
    'film' => ['screening']
],
'relationFieldHandle' => [
    '*' => 'paPackage',
    'film' => 'filmPackage'
]
```

