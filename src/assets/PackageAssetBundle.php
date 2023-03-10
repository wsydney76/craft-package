<?php

namespace wsydney76\package\assets;

use Craft;
use craft\web\AssetBundle;

/**
 * Package Assets asset bundle
 */
class PackageAssetBundle extends AssetBundle
{
    /**
     * @var string|null the directory that contains the source asset files for this asset bundle.
     * A source asset file is a file that is part of your source code repository of your Web application.
     *
     * You must set this property if the directory containing the source asset files is not Web accessible.
     * By setting this property, [[AssetManager]] will publish the source asset files
     * to a Web-accessible directory automatically when the asset bundle is registered on a page.
     *
     * If you do not set this property, it means the source asset files are located under [[basePath]].
     *
     * You can use either a directory or an alias of the directory.
     * @see publishOptions
     */
    public $sourcePath = __DIR__ . '/dist';

    /**
     * @var array list of bundle class names that this bundle depends on.
     *
     * For example:
     *
     * ```php
     * public $depends = [
     *    'yii\web\YiiAsset',
     *    'yii\bootstrap\BootstrapAsset',
     * ];
     * ```
     */
    public $depends = [];

    /**
     * @var array list of JavaScript files that this bundle contains. Each JavaScript file can be
     * specified in one of the following formats:
     *
     * - an absolute URL representing an external asset. For example,
     *   `https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` or
     *   `//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js`.
     * - a relative path representing a local asset (e.g. `js/main.js`). The actual file path of a local
     *   asset can be determined by prefixing [[basePath]] to the relative path, and the actual URL
     *   of the asset can be determined by prefixing [[baseUrl]] to the relative path.
     * - an array, with the first entry being the URL or relative path as described before, and a list of key => value pairs
     *   that will be used to overwrite [[jsOptions]] settings for this entry.
     *   This functionality is available since version 2.0.7.
     *
     * Note that only a forward slash "/" should be used as directory separator.
     */
    public $js = [
        'cpscripts.js'
    ];

    /**
     * @var array list of CSS files that this bundle contains. Each CSS file can be specified
     * in one of the three formats as explained in [[js]].
     *
     * Note that only a forward slash "/" should be used as directory separator.
     */
    public $css = [
        'cpstyles.css'
    ];
}
