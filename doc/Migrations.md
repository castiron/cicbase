## Migrations

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

[back to docs](.)