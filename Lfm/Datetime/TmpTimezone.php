<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
class Lfm_Datetime_TmpTimezone {
    private $backup;

    public function __construct($new_timezone) {
        $this->backup = date_default_timezone_get();
        if(!date_default_timezone_set($new_timezone)) {
            throw new Lfm_Datetime_TmpTimezone_Exception('タイムゾーンの設定に失敗');
        }
    }

    public function __destruct() {
        if(!date_default_timezone_set($this->backup)) {
            throw new Lfm_Datetime_TmpTimezone_Exception('タイムゾーンの復元に失敗');
        }
    }

    private function __clone() {
        throw new Lfm_Datetime_TmpTimezone_Exception('noncopyable');
    }
}
