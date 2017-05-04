<?php namespace CIC\Cicbase\Utility;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Class Tca
 * @package CIC\Cicbase\Utility
 */
class TcaUtility {
    /**
     * @param string $label For convenience
     * @param array $overrides
     * @return array
     */
    public static function standardTcaInputFieldConfig($label = '', $overrides = []) {
        $out = [
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => $label,
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ];
        ArrayUtility::mergeRecursiveWithOverrule($out, $overrides);
        return $out;
    }
}
