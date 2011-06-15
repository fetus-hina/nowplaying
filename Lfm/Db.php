<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
class Lfm_Db {
    static private $db = null;

    static public function getInstance() {
        $t = new Lfm_ProgressTimer(__METHOD__);
        if(!self::$db) {
            $db_file = realpath(dirname(__FILE__) . '/../data') . '/last-fm.sqlite';
            self::$db = Zend_Db::factory('Pdo_Sqlite', array('dbname' => $db_file));
            self::createTables();
        }
        return self::$db;
    }

    static private function createTables() {
        $t = new Lfm_ProgressTimer(__METHOD__);
        $using_tables = array('artists', 'songs', 'play_logs', 'tweet_info');
        $exist_tables = self::$db->listTables();
        foreach($using_tables as $using_table) {
            if(!in_array($using_table, $exist_tables, true)) {
                self::createTable($using_table);
            }
        }
    }

    static private function createTable($table) {
        $t = new Lfm_ProgressTimer(__METHOD__ . '(' . $table . ')');
        call_user_func(array('Lfm_Db', 'createTable' . str_replace('_', '', $table)));
    }

    static private function createTableArtists() {
        $t = new Lfm_ProgressTimer(__METHOD__);
        self::$db->query(
            self::replaceIdentifier(
                'CREATE TABLE {{artists}} (
                    {{id}}              TEXT        NOT NULL    PRIMARY KEY,
                    {{name}}            TEXT        NOT NULL,
                    {{uri}}             TEXT        NOT NULL
                )'));
    }

    static private function createTableSongs() {
        $t = new Lfm_ProgressTimer(__METHOD__);
        self::$db->query(
            self::replaceIdentifier(
                'CREATE TABLE {{songs}} (
                    {{id}}              TEXT        NOT NULL    PRIMARY KEY,
                    {{artist_id}}       TEXT        NOT NULL    REFERENCES {{artists}}({{id}}) ON DELETE RESTRICT,
                    {{title}}           TEXT        NOT NULL,
                    {{uri}}             TEXT        NOT NULL,
                    {{play_count}}      INTEGER     NOT NULL,
                    {{first_played_at}} TEXT        NOT NULL,
                    {{last_played_at}}  TEXT        NOT NULL,
                    {{last_play_id}}    TEXT        NOT NULL,
                    {{last_play_etag}}  TEXT        NOT NULL
                )'));
    }

    static private function createTablePlayLogs() {
        $t = new Lfm_ProgressTimer(__METHOD__);
        self::$db->query(
            self::replaceIdentifier(
                'CREATE TABLE {{play_logs}} (
                    {{id}}              INTEGER     NOT NULL    PRIMARY KEY /* AUTO_INCREMENT */,
                    {{song_id}}         TEXT        NOT NULL    REFERENCES {{songs}}({{id}}) ON DELETE RESTRICT,
                    {{play_at}}         TEXT        NOT NULL,
                    {{proc_at}}         TEXT        NOT NULL
                )'));
    }

    static private function createTableTweetInfo() {
        $t = new Lfm_ProgressTimer(__METHOD__);
        self::$db->query(
            self::replaceIdentifier(
                'CREATE TABLE {{tweet_info}} (
                    {{song_id}}         TEXT        NOT NULL    REFERENCES {{songs}}({{id}}) ON DELETE RESTRICT,
                    {{tweet_at}}        TEXT        NOT NULL
                )'));
    }

    static private function replaceIdentifier($sql_template) {
        return preg_replace_callback('/\{\{(.+?)\}\}/', array('Lfm_Db', 'replaceIdentifierCallback'), $sql_template);
    }

    static private function replaceIdentifierCallback(array $match) {
        return self::$db->quoteIdentifier($match[1]);
    }
}
