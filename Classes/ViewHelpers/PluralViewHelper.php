<?php namespace CIC\Cicbase\ViewHelpers;

/**
 * Render a word in singular or plural based on the number of items. Don't expect much out of the pluralization. Just
 * provided the plural form!
 *
 * @package TYPO3
 * @subpackage Fluid
 * @version
 */

class PluralViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
    public function initializeArguments()
    {
        $this->registerArgument('items', 'mixed', 'The countable items', false, null);
        $this->registerArgument('count', 'integer', 'The countable items', false, 0);
        $this->registerArgument('singular', 'string', 'The singular version', true);
        $this->registerArgument('plural', 'string', 'The plural version', false, null);
    }

    /**
     *
     */
    public function render() {
        $count = null;

        // Check items first...
        if($this->arguments['items'] !== null) {
            $count = @count($this->arguments['items']);
        }

        // ...then specified count
        if($count === null) {
            $count = intval($this->arguments['count']);
        }

        if($count == 1) {
            return $this->arguments['singular'];
        } else {
            return $this->getPlural($this->arguments['singular'], $this->arguments['plural']);
        }
    }

    /**
     * @param $singular
     * @param null $plural
     * @return string|null
     */
    protected function getPlural($singular, $plural = null) {
        if($plural === null) {
            // Super cheap pluralization, copied from here: https://stackoverflow.com/questions/1534127/pluralize-in-php
            $lastLetter = strtolower($singular[strlen($singular)-1]);
            switch($lastLetter) {
                case 'y':
                    return substr($singular,0,-1).'ies';
                case 's':
                    return $singular.'es';
                default:
                    return $singular.'s';
            }
        }
        return $plural;
    }
}
