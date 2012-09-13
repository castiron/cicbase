<?php


class Tx_Cicbase_ViewHelpers_Format_UpperCaseViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

    /**
     * @return string
     */
    public function render() {
        $text = $this->renderChildren();
        return strtoupper($text);
    }
}
?>