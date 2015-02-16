# CICBase

### Compatibility ###
* master => TYPO3 6.x
* [TYPO3_4.7.x](https://github.com/castiron/cicbase/tree/TYPO3_4.7.x) => TYPO3 4.7.x

## Features
* [Class by class storage PIDs](#storagePids)
* [Migrations](#migrations)
* [File Abstraction Layer](#fal)
* [Utilities](#utilities)
* [BucketList](#bucketlist)
* [AbstractTask & InjectionService](#abstractTask)

<a name="storagePids"></a>
### Class by class storage PIDs
With the normal Typo3/ExtBase setup, `storagePids` are set like this:

```
# Bad
plugin.tx_extName {
    persistence {
        storagePid = 181
    }
}
```
This assumes that all records created and used by your extension will all be stored in the same storage page. **This is a very bad assumption.** There are many times that your extension will use objects from another extension that should be stored in their own page, but are instead stored using your extension's `storagePid`. There are ways to solve it by setting the `storagePid` on each plugin instance by using the Behavior tab. Or somehow manually setting the `storagePid` from within the repository.

In any case, CICBase makes this easier. By just installing the CICBase extension and including the typoscript (don't forget to include the typoscript), you can set a `storagePid` on a class by class basis like this:

```
# Good
config.tx_extbase {
    persistence {
        classes {
            TYPO3\CMS\Extbase\Domain\Model\FrontendUser {
                storagePid = 979
                newRecordStoragePid = 979
            }
            VEND\Jobboard\Domain\Model\JobPost {
                storagePid = 181
                newRecordStoragePid = 181
            }
        }
    }
}
```
#### Notes
* CICBase doesn't implement the `newRecordStoragePid` setting, that's an existing ExtBase setting that you should set along with the new `storagePid` setting detected by CICBase.
* Also, this is completely backwards compatible and the old way of setting `storagePids` is still valid.

<a name="migrations"></a>
### Migrations

CICBase ships with a migration runner object that can be called from the command line. It also creates a migrations database table with two columns: version and extension key. Any TYPO3 extension can include migrations in Classes/Migration, and the CICBase migration runner will run those migrations in order and make sure they are not run more than once.

For CICBase to see your migrations, some conventions must be followed:

* Migrations should live in EXT/Classes/Migration
* Migration class names should end with a time stamp, which is what CICBase will use to sort them. For example: MyArbitraryClassNameDescribingTheMigration1402673404.
* Migration classes should extend \CIC\Cicbase\Migration\AbstractMigration.
* Migrations must implement a "run" method. Optionally, the migration can implement a "rollback" method. Migrations without a rollback method will be marked as rolledback automatically in a user tries to roll it back.
* The Migration runner should detect an SQL error after a migration is run, in which case it will not mark the migration as run. If any exception is thrown within the migration, it will not be marked as run.
* Migrations are always scoped to the context of a TYPO3 extension.

To run migrations, use the following commands:

```
# Run a migration from the CLI
./typo3/cli_dispatch.phpsh extbase migration:run ext_key

# Rollback 1 migration from the CLI
./typo3/cli_dispatch.phpsh extbase migration:rollback ext_key
```

Here is a sample migration from one of our projects:

```php
<?php
namespace CIC\Sjcert\Migration;

class FixActionCategoryActionCounts1402602721 extends \CIC\Cicbase\Migration\AbstractMigration {

	public function run() {
		$ps = $GLOBALS['TYPO3_DB']->prepare_SELECTqueryArray(
			array(
				'SELECT' => '*',
				'FROM' => 'tx_sjcert_domain_model_actioncategory',
				'WHERE' => '',
				'GROUPBY' => '',
				'ORDERBY' => '',
				'LIMIT' => ''
			)
		);
		$ps->execute(array());
		$rows = $ps->fetchAll();
		foreach($rows as $row) {
			$ps = $GLOBALS['TYPO3_DB']->prepare_SELECTqueryArray(
				array(
					'SELECT' => 'count(*) as count',
					'FROM' => 'tx_sjcert_domain_model_action',
					'WHERE' => 'action_category = :category_uid',
					'GROUPBY' => '',
					'ORDERBY' => '',
					'LIMIT' => ''
				)
			);
			$ps->execute(array(':category_uid' => $row['uid']));
			$rows = $ps->fetchAll();
			$count = $rows[0]['count'];
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'tx_sjcert_domain_model_actioncategory',
				'uid = '.$row['uid'],
				array('actions' => $count)
			);
		}
	}

	public function rollback() {
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tx_sjcert_domain_model_actioncategory',
			'1=1',
			array('actions' => 0)
		);
	}
}
?>
```

<a name="fal"></a>
### File Abstraction Layer
ExtBase does not provide user upload support with their new FAL setup. Instead we've rolled our own:
* Works with latest FAL
* Works with extbase property mapper for single or collection properties. Errors are applied to the appropriate property.
* Does real validation of size and mime type. 
* Saves uploaded files if there are other errors on the form, so users don't have to re-upload if the form fails.

Refer to the class comments on the  [FileReferenceConverter](Classes/Property/TypeConverter/FileReferenceConverter.php) for specific details.


<a name="utilities"></a>
### Utilities
Inspired by such libraries as Laravel "helpers" or even Underscore.js, there are now several utility classes in cicbase that serve simple, but useful, purposes. The classes are in [`CIC\Utility`](Classes/Utility). Take a gander at what we’ve got. I’ve found the `CIC\Utility\Arr` methods super helpful. For example:

A lot of times you don’t care whether a variable is set or not, but you need the value if it’s there. To avoid getting any warnings, you better use `isset()`:

```
if (isset($arr[$maybeIndex]) && $arr[$maybeIndex] == ‘foo’) {
  $this->runAway();
}
```

But a nicer way to do this is just:

```
if (Arr::safe($arr, $maybeIndex) == ‘foo’) {
  $this->runAway();
}
```

I know it’s not going to send us to the moon, but it certainly cleans up yer codes.

Anyway, there’s a ton of goodies in the utility classes and you should add more because you love us. 

<a name="bucketlist"></a>
### Bucket Lists

If this isn’t something you should use before you die, then I have no idea what it is.

Every so often, you need to list things according to a particular order that doesn’t make sense by just looking at it. Let’s say you need to render a list of photos with categories ordered by IDs `7, 3, 2, 5`. So photos in category `7` are listed first, then photos in category `3`, etc. How would you do this? 

Well you’d probably create buckets. Then you’d add sorted photos to each bucket and loop through the buckets in the right order to make one big list. Not too hard to fathom. 

Now let’s say you need to list news articles in the same category order, and events, and people, etc. Are you really going to do the same algorithm over and over? Of course not. 

This is what you’d do: 

```
$list = new BucketList([7,3,2,5]);
foreach ($unsortedPhotos as $photo) {
  $list->insert($photo, $photo->categoryID);
}
…
foreach ($list as $photo) {
  $currentCategoryID = $list->currentBucket();
  $this->renderMySortedPhoto($photo);
}
```

Whaaaauutt? Mind. Blown.

You can even store info about the buckets:

```
foreach ($sortedCategories as $category) {
  $order[$category->id] = $category;
}
$list = new BucketList($order, TRUE);
foreach ($unsortedPhotos as $photo) {
  $list->insert($photo, $photo->categoryID);
}
…
foreach ($list as $photo) {
  $currentCategoryID = $list->currentBucket();
  $currentCategory = $list->currentBucketInfo();
  $this-> renderMySortedPhoto($photo);
}
```

So amaze.


<a name="abstractTask"></a>
### AbstractTask and InjectionService

In ExtBase, tasks do not come with the magic injections and stuff that you normally have in a controller or view helper. It's usually a roll-your-own kinda thing. But no more. 
 
To get injections (anywhere) you can easily invoke the [InjectionService](Classes/Service/InjectionService.php):

```
$injectionService = GeneralUtility::makeInstance('CIC\Cicbase\Service\InjectionService');
$injectionService->doInjection($this);
```

This uses the tools provided by ExtBase to inject things in the same ways you expect it to in other places around the extension. That's really handy, right?

Even more handy, you can extend [AbstractTask](Classes/Scheduler/AbstractTask.php) and call `parent::initialize()` to get the injections as well as to get your extension typoscript settings. **You _must_ pass in the extension and plugin names.**

```
public function execute() { 
	parent::initialize('myext', 'default'); 
	
	// ... 
}
```
