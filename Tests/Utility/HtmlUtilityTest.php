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

        $markup = '<div style="text-align: left;"><span style="color: black;">OK WHATEVER</span></div>';
        $this->assertEquals('<div><span>OK WHATEVER</span></div>', HtmlUtility::removeAttributes($markup));
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
     * @test
     */
    public function testToRawHtmlHandlesEmptyInput() {
        $markup = '';
        $document = new \DOMDocument();
        $document->loadHTML($markup);
        $this->assertEquals($markup, HtmlUtility::toRawHtml($document));
    }

    /**
     * @test
     */
    public function testItRemovesOutermostTag() {
        $samples = [
            [
                'remove' => 'figure',
                'expect' => ['<figure><div>omgoodness</div></figure>', '<div>omgoodness</div>']
            ],
            [
                'remove' => 'div',
                'expect' => ['<div><div>omgoodness</div></div>', '<div>omgoodness</div>']
            ],
            [
                'remove' => 'div',
                'expect' => ['<div class="out"><div class="mid"><div class="in">omgoodness</div></div></div>', '<div class="mid"><div class="in">omgoodness</div></div>']
            ],
        ];
        foreach ($samples as $sample) {
            $this->assertItRemovesOuterTag($sample);
        }
    }

    /**
     * @test
     */
    public function testItRemovesOutermostTagWithMultipleChildren() {
        $samples = [
            [
                'remove' => 'div',
                'expect' => ['<div><span>Ok something</span><div>omgoodness</div></div>', '<span>Ok something</span><div>omgoodness</div>']
            ],
            [
                'remove' => 'td',
                'expect' => ['<td><div><span>Ok something</span> Whatever you guys <div>omgoodness</div></div></td>', '<div><span>Ok something</span> Whatever you guys <div>omgoodness</div></div>']
            ],
            [
                'remove' => 'a',
                'expect' => ['<a href="/dirt"><span>Ok something</span><div>omgoodness</div></a>', '<span>Ok something</span><div>omgoodness</div>']
            ],
        ];
        foreach ($samples as $sample) {
            $this->assertItRemovesOuterTag($sample);
        }
    }

    /**
     *
     */
    public function testItLeavesContentWhenRemovingOutermostTag() {
        $samples = [
            [
                'remove' => 'div',
                'expect' => ['<div>Ok something</div>', 'Ok something']
            ],
            [
                'remove' => 'div',
                'expect' => [
                    '<div>Ok something<div>omgoodness</div></div>',
                    'Ok something<div>omgoodness</div>'
                ]
            ],
        ];

        foreach ($samples as $sample) {
            $this->assertItRemovesOuterTag($sample);
        }
    }

    /**
     *
     * Helper for checking outermost tag
     * @param array $sample
     */
    protected function assertItRemovesOuterTag($sample) {
        $markup = $sample['expect'][0];
        $expected = $sample['expect'][1];
        $document = new \DOMDocument();
        $document->loadHTML($markup);
        $document->removeChild($document->doctype);
        $element = $document->getElementsByTagName($sample['remove'])->item(0);

        /**
         * Here's the call we're testing
         */
        $newElement = HtmlUtility::removeOutermostTag($element);
        $this->assertEquals(
            "<html><body>$expected</body></html>",
            trim($document->saveHTML($newElement))
        );
    }

    /**
     * @test
     */
    public function testItRemovesSpecifiedTags() {
        $samples = [
            [
                'remove' => 'span',
                'expect' => [
                    '<div><span>Ok something</span><div>omgoodness</div></div>',
                    '<div>Ok something<div>omgoodness</div></div>'
                ]
            ],
            [
                'remove' => 'a',
                'expect' => [
                    '<div><span><a href="cheese.html">Ok</a> something</span><div>omgoodness</div></div>',
                    '<div><span>Ok something</span><div>omgoodness</div></div>'
                ]
            ],
            [
                'remove' => 'div',
                'expect' => [
                    '<div class="div-1"><div class="div-2"><div class="div-3"><div class="div-4"><div class="div-5"><div class="div-6"><div class="div-7">What the</div></div></div></div></div></div>',
                    'What the'
                ]
            ],
            [
                'remove' => ['a', 'span', 'div'],
                'expect' => [
                    '<div><span><a href="cheese.html">Ok</a> something</span> <div>omgoodness</div></div>',
                    'Ok something omgoodness'
                ]
            ],
        ];

        foreach ($samples as $sample) {
            $this->assertItRemovesTag($sample);
        }
    }

    /**
     * @test
     */
    public function testItConvertsToRawHtml() {
        $document = new \DOMDocument();
        $markup = '<div>whatevs</div>';
        $document->loadHTML($markup);
        $this->assertEquals(
            $markup,
            HtmlUtility::toRawHtml($document)
        );
    }

    /**
     *
     * Helper for checking outermost tag
     * @param array $sample
     */
    protected function assertItRemovesTag($sample) {
        $markup = $sample['expect'][0];
        $expected = $sample['expect'][1];
        $tags = $sample['remove'];
        $document = new \DOMDocument();
        $document->loadHTML($markup);

        $this->assertEquals(
            $expected,
            HtmlUtility::removeTags($document, $tags)
        );
    }
}
