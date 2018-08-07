<?php namespace CIC\Cicbase\Traits\Solr;

use ApacheSolrForTypo3\Solr\Facet\Facet;
use ApacheSolrForTypo3\Solr\Facet\FacetRendererFactory;
use ApacheSolrForTypo3\Solr\Util;
use CIC\Cicbase\Domain\Model\Solr\FacetProxy;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Facetable
 * @package CIC\Cicbase\Traits\Solr
 */
trait Facetable {
    /**
     * @return \ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration
     */
    protected static function solrConfiguration() {
        return Util::getSolrConfiguration();
    }

    /**
     * @param $configuredFacets
     * @return FacetRendererFactory
     */
    protected static function getFacetRendererFactory($configuredFacets) {
        /** @var FacetRendererFactory $out */
        $out = GeneralUtility::makeInstance(
            FacetRendererFactory::class,
            $configuredFacets
        );
        return $out;
    }

    /**
     * @param $facetName
     * @param FacetRendererFactory $facetRendererFactory
     * @return Facet
     */
    protected static function getFacet($facetName, FacetRendererFactory $facetRendererFactory) {
        /** @var Facet $out */
        $out = GeneralUtility::makeInstance(Facet::class,
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
