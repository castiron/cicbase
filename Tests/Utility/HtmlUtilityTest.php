<?php namespace CIC\Cicbase\Tests\Utility;

use CIC\Cicbase\Utility\HtmlUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class HtmlUtilityTest
 * @package CIC\Cicbase\Tests\Utility
 */
Class HtmlUtilityTest extends UnitTestCase {
    /**
     *
     */
    public function testItRemovesAttributes() {
        $markup = '<div style="text-align: left;"></div>';
        $this->assertEquals('<div></div>', HtmlUtility::removeAttributes($markup));
    }

    /**
     *
     */
    public function testRemoveAttributesHandlesEmptyInputs() {
        $markup = '';
        $this->assertEquals($markup, HtmlUtility::removeAttributes($markup));
    }

    /**
     *
     */
    public function testItConvertsADomDocumentToPlainHTMLWithoutCruft() {
        $markup = '<div>omgoodness</div>';
        $document = new \DOMDocument();
        $document->loadHTML($markup);
        $this->assertEquals($markup, HtmlUtility::toRawHtml($document));


        $markup = '<p>If you want to output XML you can use the fact that <code>DOMDocument</code> is a <code>DOMNode</code> (namely: \'/\' in XPath expression), thus you can use <code>DOMNode</code> API calls on it to iterate over child nodes and call <code>saveXML()</code> on each child node. This does not output the XML declaration, and it outputs all other XML content properly.</p>';
        $document = new \DOMDocument();
        $document->loadHTML($markup);
        $this->assertEquals($markup, HtmlUtility::toRawHtml($document));
    }

    /**
     *
     */
    public function testToRawHtmlHandlesEmptyInput() {
        $markup = '';
        $document = new \DOMDocument();
        $document->loadHTML($markup);
        $this->assertEquals($markup, HtmlUtility::toRawHtml($document));
    }
}
