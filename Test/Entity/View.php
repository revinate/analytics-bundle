<?php
namespace Revinate\AnalyticsBundle\Test\Entity;

class View {
    protected $date;
    protected $browser;
    protected $device;
    protected $views;
    protected $siteId;
    /** @var  Tag[] */
    protected $tags;

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * @param mixed $browser
     */
    public function setBrowser($browser)
    {
        $this->browser = $browser;
    }

    /**
     * @return mixed
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param mixed $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }

    /**
     * @return mixed
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @param mixed $views
     */
    public function setViews($views)
    {
        $this->views = $views;
    }

    /**
     * @param Tag[] $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return Tag[]
     */
    public function getTags() {
        return $this->tags;
    }

    /**
     * @return mixed
     */
    public function getSiteId()
    {
        return $this->siteId;
    }

    /**
     * @param mixed $siteId
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
    }

    /**
     * @return array
     */
    public function toArray() {
        $tagDocuments = array();
        foreach ($this->getTags() as $tag) {
            $tagDocuments[] = $tag->toArray();
        }
        return array(
            "device" => $this->getDevice(),
            "browser" => $this->getBrowser(),
            "siteId" => $this->getSiteId(),
            "views" => $this->getViews(),
            "date" => $this->getDate()->format("c"),
            "tags" => $tagDocuments,
        );
    }
}