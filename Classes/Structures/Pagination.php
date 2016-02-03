<?php

namespace CIC\Cicbase\Structures;

use CIC\Cicbase\Utility\Arr;

class Pagination
{
    const MODE_SIMPLE = 'simple';

    /** @var integer */
    protected $current;
    /** @var integer */
    protected $last;
    /** @var integer */
    protected $pageSize;
    /** @var integer */
    protected $surrounding = 2;
    /** @var integer */
    protected $mode;
    /** @var array */
    protected $cachedResult = array();

    /**
     * @param integer $totalItems
     * @param integer $pageSize
     * @param integer $currentPage
     * @param string $mode
     * @throws \Exception
     */
    public function __construct($totalItems, $pageSize, $currentPage, $mode = self::MODE_SIMPLE)
    {
        if ($pageSize == 0) {
            throw new \Exception('Cannot paginate without a $pageSize parameter');
        }
        $this->current = $currentPage;
        $this->last = ceil($totalItems / $pageSize);
        $this->pageSize = $pageSize;
        $this->mode = $mode;
    }

    /**
     * Returns the list of pages needed to display pagination.
     * The result here may be different depending on the mode.
     *
     * @return array
     */
    public function getPages()
    {
        return $this->initialize();
    }

    /**
     * @return bool
     */
    public function getHasPages()
    {
        $this->initialize();
        return count($this->cachedResult) > 1;
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
     * @return bool|int
     */
    public function getNextPage()
    {
        return $this->current == $this->last ? false : $this->current + 1;
    }

    /**
     * @return bool|int
     */
    public function getPrevPage()
    {
        return $this->current == 1 ? false : $this->current - 1;
    }


    /**
     * Returns the limit clause value for use in a SQL statement.
     * This can include the relevant offset if the current page
     * is greater than 1.
     *
     * @return string
     */
    public function getSqlLimit()
    {
        $ps = $this->pageSize;
        $p = $this->current;
        return $p == 1 ? $ps : $ps * ($p - 1) . ',' . $ps;
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->current;
    }

    /**
     * @return float|int
     */
    public function getLastPage()
    {
        return $this->last;
    }

    /**
     * Generates our resulting pagination pages once.
     */
    protected function initialize()
    {
        if (!empty($this->cachedResult)) {
            return $this->cachedResult;
        }
        switch ($this->mode) {
            case self::MODE_SIMPLE:
                $this->cachedResult = $this->makeSimpleArray();
                break;
        }
        return $this->cachedResult;
    }


    /**
     * Creates an array of pages like
     *
     * 1,2,3,'…',10,11,12,13,14,'…',99,100,101
     *
     * where there are 101 pages and the current page is 12.
     *
     * There is no meta data here, just the page numbers and ellipses.
     *
     * @return array
     */
    protected function makeSimpleArray()
    {
        $ellipsis = '…';

        if ($this->last == 0) {
            return array();
        }

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