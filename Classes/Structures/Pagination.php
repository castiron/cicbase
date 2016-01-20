<?php

namespace CIC\Cicbase\Structures;

use CIC\Cicbase\Utility\Arr;

class Pagination
{
    /** @var integer */
    protected $total;
    /** @var integer */
    protected $pageSize;
    /** @var integer */
    protected $current;
    /** @var integer */
    protected $last;
    /** @var integer */
    protected $surrounding = 2;

    /**
     * @param integer $totalPages
     * @param integer $pageSize
     * @param integer $currentPage
     * @throws \Exception
     */
    public function __construct($totalPages, $pageSize, $currentPage = 1)
    {
        if ($currentPage < 1) {
            throw new \Exception('Current page must be 1 or more');
        }

        $this->total = $totalPages;
        $this->pageSize = $pageSize;
        $this->current = $currentPage;
        $this->last = ceil($totalPages / $pageSize);
    }

    /**
     * Set number of pages to show around the current page.
     *
     * @param $size
     */
    public function surrounding($size)
    {
        $this->surrounding = $size;
    }


    /**
     * Creates an array of pages like
     *
     * 1,2,3,'...',10,11,12,13,14,'...',99,100,101
     *
     * where there are 101 pages and the current page is 12.
     *
     * There is no meta data here, just the page numbers and ellipses.
     *
     * @param string $ellipsis
     * @return array
     */
    public function makeSimpleArray($ellipsis = '...')
    {
        // learn our bounds and protect for going too far at either end
        $minMiddle = max(1, $this->current - $this->surrounding);
        $maxMiddle = min($this->last, $this->current + $this->surrounding);
        $maxFirstPages = min($this->surrounding + 1, $this->last);
        $minLastPages = max($this->last - $this->surrounding, 1);

        // create our naive groupings
        $firstPages = range(1, $maxFirstPages);
        $lastPages = range($minLastPages, $this->last);
        $middlePages = self::merge(
            range($minMiddle, $this->current),
            range($this->current, $maxMiddle)
        );

        // No ellipses needed if $middlePages spans all
        if ($minMiddle == 1 && $maxMiddle == $this->last) {
            return $middlePages;
        }

        // merge the page groupings or put in an ellipsis if we need to
        $res1 = self::mergeOrEllipsify($firstPages, $middlePages, $ellipsis);
        $res2 = self::mergeOrEllipsify($res1, $lastPages, 'ellipsis2');

        // since merging removes duplicates, our second $ellipsis would be removed
        // to avoid that (or any other solution), we just used a placeholder
        $key = array_search('ellipsis2', $res2);
        if ($key) {
            $res2[$key] = $ellipsis;
        }

        return $res2;
    }

    protected static function mergeOrEllipsify($first, $second, $ellipsis)
    {
        if (max($first) + 1 >= min($second)) {
            return self::merge($first, $second);
        }
        return self::merge($first, $ellipsis, $second);
    }

    protected static function merge()
    {
        $args = func_get_args();
        self::forceArrayOfArrays($args);
        return array_values(array_unique(call_user_func_array('array_merge', $args)));
    }

    protected static function forceArrayOfArrays(&$arr)
    {
        array_walk($arr, function (&$arg) {
            if (!is_array($arg)) {
                $arg = array($arg);
            }
        });
    }
}