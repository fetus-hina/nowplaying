<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
class Lfm_ProgressTimer {
    static private
        $indent = 0;

    private
        $label  = null,
        $time   = null;

    public function __construct($label) {
        printf("%s%s ...(start)\n", str_repeat('  ', self::$indent), $label);
        ++self::$indent;
        $this->label = $label;
        $this->time = microtime(true);
    }

    public function __destruct() {
        --self::$indent;
        printf("%s%s ...(end) %.3f sec\n", str_repeat('  ', self::$indent), $this->label, microtime(true) - $this->time);
    }

    private function __clone() {
    }
}
