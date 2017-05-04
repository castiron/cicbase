<?php namespace CIC\Cicbase\Tests\Utility;

use CIC\Cicbase\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class FrontendUserUtilityTest
 * @package CIC\Cicbase\Tests\Utility
 */
class FrontendUserUtilityTest extends UnitTestCase {


    /**
     * @test
     */
    public function itAllowsHideAtLogin() {
        $hideAtLoginPseudoGroup = FrontendUserUtility::PSEUDO_GROUP_HIDE_AT_LOGIN;
        /**
         * User's not logged in
         */
        static::setupNotAuthenticatedUser();
        $this->assertTrue(FrontendUserUtility::currentUserMatchesGroups([$hideAtLoginPseudoGroup]));

        /**
         * User is logged in
         */
        static::setupAuthenticatedUser();
        $this->assertFalse(FrontendUserUtility::currentUserMatchesGroups([$hideAtLoginPseudoGroup]));
    }

    /**
     * @test
     */
    public function itAllowsShowAtAnyLogin() {
        $showAtLoginPseudoGroup = FrontendUserUtility::PSEUDO_GROUP_SHOW_AT_ANY_LOGIN;
        /**
         * User's logged in
         */
        static::setupAuthenticatedUser();
        $this->assertTrue(FrontendUserUtility::currentUserMatchesGroups([$showAtLoginPseudoGroup]));

        /**
         * User's not logged in
         */
        static::setupNotAuthenticatedUser();
        $this->assertFalse(FrontendUserUtility::currentUserMatchesGroups([$showAtLoginPseudoGroup]));
    }

    /**
     *
     */
    public function itReturnsTrueForEmptyGroupSet() {
        /**
         * User's logged in
         */
        static::setupAuthenticatedUser();
        $this->assertTrue(FrontendUserUtility::currentUserMatchesGroups());
        $this->assertTrue(FrontendUserUtility::currentUserMatchesGroups([]));

        /**
         * User's not logged in
         */
        static::setupNotAuthenticatedUser();
        $this->assertTrue(FrontendUserUtility::currentUserMatchesGroups());
        $this->assertTrue(FrontendUserUtility::currentUserMatchesGroups([]));
    }

    /**
     * @test
     */
    public function itMatchesSingleGroupWithCurrentUser() {
        $groups = [1, 2, 3];
        static::setupUserWithGroups($groups);
        foreach ($groups as $group) {
            $this->assertTrue(FrontendUserUtility::currentUserMatchesGroups([$group]));
        }
    }

    /**
     * @test
     */
    public function itDoesntMatchSingleGroupWithCurrentUser() {
        $groups = [4, 5, 6];
        static::setupUserWithGroups($groups);
        foreach ([1, 2, 3] as $group) {
            $this->assertFalse(FrontendUserUtility::currentUserMatchesGroups([$group]));
        }
    }

    /**
     * @test
     */
    public function itMatchesMultipleGroupsWithCurrentUser() {
        $firstGroups = [2, 10, 11, 12];

        $secondGroups = [
            $firstGroups,
            [1, 2, 8],
            [13, 12],
            [4, 5, 6, 7, 8, 9, 11],
        ];
        static::setupUserWithGroups($firstGroups);
        foreach ($secondGroups as $group) {
            $this->assertTrue(FrontendUserUtility::currentUserMatchesGroups($group));
        }
    }

    /**
     * @test
     */
    public function itDoesntMatchMultipleGroupsWithCurrentUser() {
        $firstGroups = [
            [4, 5, 6],
            [7, 8, 9],
            [10, 11, 12],
        ];

        $secondGroups = [
            [1, 2, 3],
            [13, 14, 15],
            [16, 17, 18],
        ];
        foreach ($secondGroups as $groups) {
            static::setupUserWithGroups($groups);
            foreach ($firstGroups as $group) {
                $this->assertFalse(FrontendUserUtility::currentUserMatchesGroups($group));
            }
        }
    }

    public function itDoesntCareAboutGroupValueType() {
        $group = '2';
        /**
         * User's not logged in
         */
        static::setupNotAuthenticatedUser();
        $this->assertTrue(FrontendUserUtility::currentUserMatchesGroups([$group]));

        /**
         * User is logged in
         */
        static::setupAuthenticatedUser();
        $this->assertFalse(FrontendUserUtility::currentUserMatchesGroups([$group]));
    }

    // ========================= //

    /**
     * @param array $groups
     */
    protected static function setupUserWithGroups($groups = []) {
        static::setupFakeFeUser(['usergroup' => implode(',', $groups)]);
    }

    /**
     *
     */
    protected static function setupAuthenticatedUser() {
        static::setupUserWithGroups([1, 2, 3]);
    }

    /**
     *
     */
    protected static function setupNotAuthenticatedUser() {
        static::setupFakeFeUser([], false);
    }

    /**
     * @param array $groups
     */
    protected static function makeUserWithGroups($groups = []) {
        return static::setupFakeFeUser(['usergroup' => implode(',', $groups)]);
    }

    /**
     * Reset the world
     */
    protected static function trashTsfe() {
        unset($GLOBALS['TSFE']);
    }

    /**
     * @param array $fields
     * @param bool $authenticated
     */
    protected static function setupFakeFeUser($fields = [], $authenticated = true) {
        static::trashTsfe();
        global $TSFE;

        $feUser = new \stdClass();
        $feUser->user = $authenticated ? array_merge(static::defaultFeUserFields(), $fields) : null;

        $TSFE = new \stdClass();
        $TSFE->fe_user = $feUser;
    }

    /**
     * @return array
     */
    protected function defaultFeUserFields() {
        return [
            'uid' => '323',
            'pid' => '1',
            'username' => 'dmcgill',
            'password' => '21897890adflhajskflhd273849137849014',
            'usergroup' => '',
            'name' => 'David McGillicuddy',
            'email' => 'fake@my-domain.tld',
            'crdate' => time(),
        ];
    }
}
