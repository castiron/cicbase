<?php namespace CIC\Cicbase\Utility;

use TYPO3\CMS\Core\Error\Exception;

/**
 * Some basic utility methods for manipulating HTML
 * Class HtmlUtility
 * @package CIC\Cicbase\Utility
 */
class HtmlUtility {
    /**
     * @param \DOMDocument|string $subject
     * @param array $attributes
     * @return string
     */
    public static function removeAttributes($subject, array $attributes = array()) {
        /**
         * Update the document by applying a call to every element on it
         */
        $updatedDocument = static::onAllElements($subject, function (\DOMElement $element) use ($attributes) {
            /**
             * If an empty array is passed, remove every attribute
             */
            if (!count($attributes)) {
                static::removeAllAttributes($element);
                return;
            }

            /**
             * Otherwise remove only those specified
             */
            foreach ($attributes as $attribute) {
                $element->removeAttribute($attribute);
            }
        });

        return static::toRawHtml($updatedDocument);
    }

    /**
     * @param \DOMDocument|string $subject
     * @param array $tags
     * @return string
     * @throws Exception
     */
    public static function removeTags($subject, array $tags = array()) {
        if (!count($tags)) {
            throw new Exception('You must specify the tags you want to remove');
        }

        /**
         * Update the document by applying a call to every element on it
         */
        foreach ($tags as $tag) {
            static::onAllElements($subject, function (\DOMElement $element) {
                /**
                 * Remove the outermost tag (the target tag)
                 */
                static::removeOutermostTag($element);
            }, $tag);
        }

        return static::toRawHtml($subject);
    }

    /**
     * @param \DOMElement $element
     * @return \DOMElement
     */
    public static function removeOutermostTag(\DOMElement $element) {
        $sibling = $element->firstChild;
        do {
            $next = $element->nextSibling;
            $element->parentNode->insertBefore($sibling, $from);
        } while ($sibling = $next);

        $element->parentNode->removeChild($element);
        return $element;
    }

    /**
     * Convert a DOMDocument to a string without the <html><body> wraps
     * @param \DOMDocument $document
     * @return string
     */
    public static function toRawHtml(\DOMDocument $document) {
        $out = '';
        /** @var \DOMElement $node */
        foreach ($document->childNodes as $node) {
            $out .= $document->saveHTML($node);
        }
        return preg_replace('~</?(html|body)>~', '', $out);
    }

    /**
     * @param \DOMElement $element
     * @return \DOMElement
     */
    protected function removeAllAttributes(\DOMElement $element) {
        $attributes = $element->attributes;
        while ($attributes->length) {
            $element->removeAttribute($attributes->item(0)->name);
        }
        return $element;
    }

    /**
     * @param $str
     * @return \DOMDocument
     */
    protected static function getDomDocumentFromString($str) {
        $document = new \DOMDocument();
        $document->loadHTML($str);
        return $document;
    }

    /**
     * @param \DOMDocument|string $subject
     * @param callable $closure
     * @param string $tagName A tag to which to scope the activity
     * @return \DOMDocument
     * @throws Exception
     */
    public function onAllElements($subject, callable $closure, $tagName = '*') {
        /**
         * Normalize input
         */
        if (is_string($subject)) {
            $subject = static::getDomDocumentFromString($subject);
        }

        /**
         * Toss if we couldn't do it
         */
        if (!$subject instanceof \DOMDocument) {
            throw new Exception('Could not parse document');
        }

        /** @var \DOMElement $element */
        foreach ($subject->getElementsByTagName($tagName) as $element) {
            $closure($element);
        }

        /**
         * For chaining if you wants to
         */
        return $subject;
    }
}
