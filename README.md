# CICBase

### Compatibility ###
* master => TYPO3 6.x
* [TYPO3_4.7.x](https://github.com/castiron/cicbase/tree/TYPO3_4.7.x) => TYPO3 4.7.x

## Features
* [Class by class storage PIDs](#storagePids)
* [Migrations](#migrations)
* [File Abstraction Layer](#fal)

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
...to be written

