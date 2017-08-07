# CICBase

Please note: if you want the S3 upload functionality that accompanies Cicbase's FileRepository, please be sure to
add `composer require aws/aws-sdk-php` in your TYPO3 project, and add the following to 
typo3conf/AdditionalConfiguration.php (unless you're in TYPO3 v8):

```
/**
 * Include composer-provided packages
 */
$composerAutoload = PATH_site . '../vendor/autoload.php';
if (!is_file($composerAutoload)) {
	throw new Exception('Could not load composer dependencies. Please run "composer install" in the project root.');
}
require_once $composerAutoload;

```

### Compatibility ###
* [master](https://github.com/castiron/cicbase/tree/master) => TYPO3 7.6.x
* [TYPO3_6.2.x](https://github.com/castiron/cicbase/tree/TYPO3_6.2.x) => TYPO3 6.2.x
* [TYPO3_4.7.x](https://github.com/castiron/cicbase/tree/TYPO3_4.7.x) => TYPO3 4.7.x

### [Docs](doc)