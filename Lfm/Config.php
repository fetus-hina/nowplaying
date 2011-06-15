<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
class Lfm_Config {
    private $data;

    static public function getInstance() {
        $t = new Lfm_ProgressTimer(__METHOD__);
        static $instance = null;
        if(is_null($instance)) {
            $instance = new self();
        }
        return $instance;
    }

    private function __construct() {
        $t = new Lfm_ProgressTimer(__METHOD__);
        $this->data =
            array(
                'lastfm'    => $this->load('lastfm'),
                'twitter'   => $this->load('twitter'),
                'bitly'     => $this->load('bitly'));
    }

    public function __get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    private function load($section) {
        $t = new Lfm_ProgressTimer(__METHOD__);
        return
            new Zend_Config_Ini(
                dirname(__FILE__) . '/../config/lastfm.ini',
                $section);
    }
}
