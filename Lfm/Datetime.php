<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
class Lfm_Datetime {
    const FORMAT = 'Y-m-d H:i:sO';

    private
        $unixtime = 0;

    public function __construct($data = 0) {
        $this->set($data);
    }

    static public function factory($data = 0) {
        return new self($data);
    }

    public function getInt() {
        return (int)$this->unixtime;
    }

    public function getInteger() {
        return (int)$this->unixtime;
    }

    public function getUtc() {
        return $this->getLocal('UTC');
    }

    public function getLocal($tz = null) {
        $raii = new Lfm_Datetime_TmpTimezone(is_null($tz) ? date_default_timezone_get() : $tz);
        return date(self::FORMAT, (int)$this->unixtime);
    }

    public function __toString() {
        return $this->getLocal();
    }

    public function set($data) {
        if(is_numeric($data)) {
            $this->setInteger($data);
        } else {
            $this->setString($data);
        }
        return $this;
    }

    public function setInteger($int) {
        $this->unixtime = (int)$int;
        return $this;
    }

    public function setString($str) {
        if(!$this->unixtime = strtotime((string)$str)) {
            throw new Lfm_Datetime_Exception('日付の解析に失敗');
        }
        return $this;
    }
}
