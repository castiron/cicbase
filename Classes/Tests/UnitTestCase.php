<?php namespace CIC\Cicbase\Tests;

use TYPO3\CMS\Core\Tests\UnitTestCase as TYPO3UnitTestCase;

/**
 * Class UnitTestCase
 * @package CIC\TYPO3\Tests
 */
abstract class UnitTestCase extends TYPO3UnitTestCase
{
    /**
     * Backing up globals wreaks havoc because there are certain ext_localconfs that contain closures...
     *
     * @var bool
     */
    protected $backupGlobals = false;
}
