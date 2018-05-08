<?php namespace CIC\Cicbase\Traits\Extbase\Repository;

/**
 * Trait FindableByUids
 *
 * You can include this in an Extbase repository class to get findByUids() on there
 *
 * @package CIC\Cicbase\Traits\Extbase\Repository
 */
trait FindableByUids
{
    /**
     * @param array $uids
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByUids($uids = [], $returnRawQueryResult = false)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(FALSE);
        $query->getQuerySettings()->setRespectSysLanguage(FALSE);
        return $query->matching($query->in('uid', $uids))->execute($returnRawQueryResult);
    }
}
