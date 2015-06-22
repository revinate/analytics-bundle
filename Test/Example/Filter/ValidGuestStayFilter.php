<?php

namespace Revinate\AnalyticsBundle\Test\Example\Filter;

use Revinate\AnalyticsBundle\Filter\AbstractCustomFilter;
use Revinate\SharedBundle\Elasticsearch\Entity\GRM\GuestStay;
use Revinate\SharedBundle\Repository\GuestListImportTranslationInternalTypeRepository;

class ValidGuestStayFilter extends AbstractCustomFilter {

    /**
     * @return string
     */
    public function getName() {
        return 'valid_guest_stay';
    }

    /**
     * @param null $value
     * @return \Elastica\Filter\AbstractFilter
     */
    public function getFilter($value = null) {
        $boolAnd = new \Elastica\Filter\BoolAnd();
        $validGuestStayFilter = new \Elastica\Filter\Terms(GuestStay::ATTRIBUTE_NAME_CONFIRMATION_STATUS, GuestListImportTranslationInternalTypeRepository::getValidTypes());
        $boolAnd->addFilter($validGuestStayFilter);
        return $boolAnd;
    }
}