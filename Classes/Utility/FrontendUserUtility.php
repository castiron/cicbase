<?php namespace CIC\Cicbase\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FrontendUserUtility
 * @package CIC\Cicbase\Utility
 */
class FrontendUserUtility {
    const PSEUDO_GROUP_HIDE_AT_LOGIN = -1;
    const PSEUDO_GROUP_SHOW_AT_ANY_LOGIN = -2;

    /**
     * @return array
     */
    public static function currentUser() {
        if (!static::userSessionExists()) {
            return [];
        }
        return $GLOBALS['TSFE']->fe_user->user;
    }

    /**
     * @return array
     */
    public static function currentUserGroups() {
        $user = static::currentUser();
        if (!$user) {
            return [];
        }
        return array_filter(GeneralUtility::trimExplode(',', $user['usergroup'])) ?: [];
    }

    /**
     * Returns true if any of the following happen:
     *  - $groups is empty
     *  - current user not logged in and "hide at login" pseudo-group is provided
     *  - current user is logged in and "show at any login" pseudo-group is provided
     *  - current user has at least one of the groups from $groups
     *
     * @param array $groups
     * @return bool
     */
    public static function currentUserMatchesGroups($groups = []) {
        /**
         * There are no groups. This matches anyone.
         */
        if (count($groups) === 0) {
            return true;
        }

        /**
         * This is not a logged-in user. Their only chance to match this group set is if it has "Hide at login"
         */
        if (!static::userSessionExists()) {
            return in_array(static::PSEUDO_GROUP_HIDE_AT_LOGIN, $groups);
        }

        /**
         * There is a current user with groups on it
         */
        if (in_array(static::PSEUDO_GROUP_SHOW_AT_ANY_LOGIN, $groups)) {
            return true;
        }

        /**
         * Does user have one of the groups in the list?
         */
        if (count(array_intersect(static::currentUserGroups(), $groups)) > 0) {
            return true;
        }

        /**
         * No more chance to survive; make your time.
         */
        return false;
    }

    /**
     * @return bool
     */
    public static function userSessionExists() {
        return $GLOBALS['TSFE'] && $GLOBALS['TSFE']->fe_user && $GLOBALS['TSFE']->fe_user->user['uid'];
    }
}
