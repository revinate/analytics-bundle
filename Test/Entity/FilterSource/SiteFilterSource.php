<?php

namespace Revinate\AnalyticsBundle\Test\Entity\FilterSource;

use Revinate\AnalyticsBundle\FilterSource\AbstractFilterSource;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SiteFilterSource extends AbstractFilterSource {

    protected static $sites = array(
        array("id" => 1, "name" => "google.com", "slug" => "google"),
        array("id" => 2, "name" => "yahoo.com", "slug" => "yahoo"),
        array("id" => 3, "name" => "bing.com", "slug" => "bing"),
        array("id" => 4, "name" => "altavista.com", "slug" => "alta"),
        array("id" => 5, "name" => "duckduckgo.com", "slug" => "duck"),
        array("id" => 6, "name" => "facebook.com", "slug" => "fb"),
        array("id" => 7, "name" => "dogpile.com", "slug" => "dpile"),
        array("id" => 8, "name" => "ask.com", "slug" => "ask"),
        array("id" => 9, "name" => "aol.com", "slug" => "aol"),
        array("id" => 10, "name" => "blekko.com", "slug" => "blek"),
    );

    protected function getNameColumn() {
        return "name";
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param $name
     * @return self
     */
    public static function create(ContainerInterface $container, $name)
    {
        return new self($container, $name);
    }

    /**
     * Filter Name
     * @return string
     */
    public function getReadableName()
    {
        return "Visited Site";
    }

    /**
     * @param string|int $id
     * @return array
     */
    public function get($id)
    {
        foreach (self::$sites as $site) {
            if ($site["id"] == $id) {
                return $site;
            }
        }
    }

    /**
     * @param string $query
     * @param $page
     * @param $pageSize
     * @return array
     */
    public function getByQuery($query, $page, $pageSize)
    {
        if (empty($query)) {
            return array();
        }
        $matches = array();
        foreach (self::$sites as $site) {
            if (strpos($site["name"], $query) !== false) {
                $matches[] = $site;
            }
        }
        return $matches;
    }

}