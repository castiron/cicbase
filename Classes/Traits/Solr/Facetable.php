<?php namespace CIC\Cicbase\Traits\Solr;

use CIC\Cicbase\Domain\Model\Solr\FacetProxy;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Facetable
 * @package CIC\Cicbase\Traits\Solr
 */
trait Facetable {
    /**
     * @return array
     */
    protected static function solrConfiguration() {
        return \tx_solr_Util::getSolrConfiguration();
    }

    /**
     * @param $configuredFacets
     * @return \Tx_Solr_Facet_FacetRendererFactory
     */
    protected static function getFacetRendererFactory($configuredFacets) {
        /** @var \Tx_Solr_Facet_FacetRendererFactory $out */
        $out = GeneralUtility::makeInstance(
            'Tx_Solr_Facet_FacetRendererFactory',
            $configuredFacets
        );
        return $out;
    }

    /**
     * @param $facetName
     * @param \Tx_Solr_Facet_FacetRendererFactory $facetRendererFactory
     * @return \Tx_Solr_Facet_Facet
     */
    protected static function getFacet($facetName, \Tx_Solr_Facet_FacetRendererFactory $facetRendererFactory) {
        /** @var \Tx_Solr_Facet_Facet $out */
        $out = GeneralUtility::makeInstance('Tx_Solr_Facet_Facet',
            $facetName,
            $facetRendererFactory->getFacetInternalType($facetName)
        );
        return $out;
    }

    /**
     * @return array
     */
    protected static function getFacets() {
        $out = [];
        $config = static::solrConfiguration();
        $configuredFacets = $config['search.']['faceting.']['facets.'];
        $facetRendererFactory = static::getFacetRendererFactory($configuredFacets);
        foreach ($configuredFacets as $facetName => $facetConfiguration) {
            $facetName = substr($facetName, 0, -1);
            $facet = static::getFacet($facetName, $facetRendererFactory);

            if (
                (isset($facetConfiguration['includeInAvailableFacets']) && $facetConfiguration['includeInAvailableFacets'] == '0')
                || !$facet->isRenderingAllowed()
            ) {
                // don't render facets that should not be included in available facets
                // or that do not meet their requirements to be rendered
                continue;
            }
            $out[$facetName] = FacetProxy::get($facet);
        }
        return $out;
    }
}
