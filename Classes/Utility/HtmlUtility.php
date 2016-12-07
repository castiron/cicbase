<?php namespace CIC\Cicbase\Utility;

use TYPO3\CMS\Core\Error\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * @param array|string $tags
     * @return string
     * @throws Exception
     */
    public static function removeTags($subject, $tags = array()) {
        if (!$subject) {
            return '';
        }

        /**
         * Normalize tags
         */
        if (is_string($tags)) {
            $tags = GeneralUtility::trimExplode(',', $tags);
        }

        /**
         * Normalize subject
         */
        if (is_string($subject)) {
            $subject = static::getDomDocumentFromString($subject);
        }

        if (!count($tags)) {
            return static::toRawHtml($subject);
        }

        /**
         * Update the document by applying a call to every element on it
         */
        foreach ($tags as $tag) {
            if (!$tag) {
                continue;
            }
            while (static::hasTag($subject, $tag)) {
                static::onAllElements($subject, function (\DOMElement $element) {
                    /**
                     * Remove the outermost tag (the target tag)
                     */
                    static::removeOutermostTag($element);
                }, $tag);
            }
        }

        return static::toRawHtml($subject);
    }

    /**
     * @param \DOMDocument|\DOMElement $subject
     * @param string $tag
     * @return bool
     */
    public static function hasTag($subject, $tag = '') {
        return $tag
            && is_object($subject)
            && $subject->getElementsByTagName($tag)->length;
    }

    /**
     * @param \DOMElement $element
     * @return \DOMElement
     */
    public static function removeOutermostTag(\DOMElement $element) {
        $parent = $element->parentNode;
        $sibling = $element->firstChild;
        do {
            $next = $sibling->nextSibling;
            $element->parentNode->insertBefore($sibling, $element);
        } while ($sibling = $next);

        $parent->removeChild($element);
        return $sibling;
    }

    /**
     * Convert a DOMDocument to a string without the <html><body> wraps
     * @param \DOMDocument $document
     * @return string
     */
    public static function toRawHtml(\DOMDocument $document) {
        if($document->doctype) {
            $document->removeChild($document->doctype);
        }
        return trim(preg_replace('~</?(html|body)>~', '', $document->saveHTML()));
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
    public static function onAllElements($subject, callable $closure, $tagName = '*') {
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
        $elements = $subject->getElementsByTagName($tagName);
        foreach ($elements as $element) {
            $closure($element);
        }

        /**
         * For chaining if you wants to
         */
        return $subject;
    }
}
