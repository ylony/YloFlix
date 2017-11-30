<?php

namespace Ylony\YloFlixBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Episode
 *
 * @ORM\Table(name="episode")
 * @ORM\Entity(repositoryClass="Ylony\YloFlixBundle\Repository\EpisodeRepository")
 */
class Episode
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="showid", type="integer")
     */
    private $showid;

    /**
     * @var int
     *
     * @ORM\Column(name="saison", type="integer")
     */
    private $saison;

    /**
     * @var int
     *
     * @ORM\Column(name="episode", type="integer")
     */
    private $episode;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="sublang", type="string", length=255)
     */
    private $sublang;

    /**
     * @var string
     *
     * @ORM\Column(name="str", type="string", length=255)
     */
    private $str;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set saison
     *
     * @param integer $saison
     *
     * @return Episode
     */
    public function setSaison($saison)
    {
        $this->saison = $saison;

        return $this;
    }

    /**
     * Get saison
     *
     * @return int
     */
    public function getSaison()
    {
        return $this->saison;
    }

    /**
     * Set showid
     *
     * @param integer $showid
     *
     * @return Episode
     */
    public function setShowid($showid)
    {
        $this->showid = $showid;

        return $this;
    }

    /**
     * Get showid
     *
     * @return int
     */
    public function getShowid()
    {
        return $this->showid;
    }
    
    /**
     * Set episode
     *
     * @param integer $episode
     *
     * @return Episode
     */
    public function setEpisode($episode)
    {
        $this->episode = $episode;

        return $this;
    }

    /**
     * Get episode
     *
     * @return int
     */
    public function getEpisode()
    {
        return $this->episode;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Episode
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Episode
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set sublang
     *
     * @param string $sublang
     *
     * @return Episode
     */
    public function setSublang($sublang)
    {
        $this->sublang = $sublang;

        return $this;
    }

    /**
     * Get sublang
     *
     * @return string
     */
    public function getSublang()
    {
        return $this->sublang;
    }

    /**
     * Set str
     *
     * @param string $str
     *
     * @return Episode
     */
    public function setStr($str)
    {
        $this->str = $str;

        return $this;
    }

    /**
     * Get str
     *
     * @return string
     */
    public function getStr()
    {
        return $this->str;
    }
}

