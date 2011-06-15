<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
class Lfm_Song {
    private
        $artist     = null, // Lfm_Artist
        $title      = null,
        $link       = null, // Zend_Uri
        $time       = null, // Lfm_Datetime
        $play_id    = null; // Lfm_Uuid

    public function __construct(
        Lfm_Artist $artist = null,
        $title = null,
        Zend_Uri $link = null,
        Lfm_Datetime $time = null,
        Lfm_Uuid $play_id)
    {
        $this
            ->setArtist($artist)
            ->setTitle($title)
            ->setLink($link)
            ->setTime($time)
            ->setPlayId($play_id);
    }

    public function getUuid() {
        return
            ($uri = $this->getLink())
                ? Lfm_Uuid::factoryV5(Lfm_Uuid::NAMESPACE_URL, $uri->__toString())
                : new Lfm_Uuid(Lfm_Uuid::NIL_UUID);
    }

    public function getArtist() {
        return $this->artist;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getTime() {
        return $this->time;
    }

    public function getLink() {
        return $this->link;
    }

    public function getPlayId() {
        return $this->play_id;
    }

    public function setArtist(Lfm_Artist $artist = null) {
        $this->artist = $artist;
        return $this;
    }

    public function setTitle($title) {
        $this->title = is_null($title) ? null : (string)$title;
        return $this;
    }

    public function setLink(Zend_Uri $uri = null) {
        $this->link = $uri;
        return $this;
    }

    public function setTime(Lfm_Datetime $time = null) {
        $this->time = $time;
        return $this;
    }

    public function setPlayId(Lfm_Uuid $id = null) {
        $this->play_id = $id;
        return $this;
    }
}
