<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
class Lfm_Artist {
    private
        $name = null,
        $link = null;   // Zend_Uri

    public function __construct($name = null, Zend_Uri $link = null) {
        $this
            ->setName($name)
            ->setLink($link);
    }

    public function getUuid() {
        if(is_null($this->name) || $this->name === '') {
            return new Lfm_Uuid(Lfm_Uuid::NIL_UUID);
        }
        return Lfm_Uuid::factoryV5(Lfm_Uuid::NAMESPACE_URL, $this->getLink()->__toString());
    }

    public function getName() {
        return $this->name;
    }

    public function getLink() {
        return $this->link;
    }

    public function setName($name) {
        $this->name = is_null($name) ? null : (string)$name;
        return $this;
    }

    public function setLink(Zend_Uri $link = null) {
        $this->link = $link;
        return $this;
    }
}
