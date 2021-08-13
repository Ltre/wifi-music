#!/usr/local/php/bin/php
<?php

include 'hehe/config.php';
include 'hehe/lib.php';
include 'hehe/dwHttp.php';
include 'hehe/Model.php';

class M3uToTable {
    
    var $batch;
    var $http;
    var $sqlite;
    
    function __construct(){
        $this->http = new dwHttp;
        $this->initDB();
    }
    
    
    function initDB(){
        @mkdir('collect');
        $this->sqlite = [
            'song' => new Model("sqlite/MyMusicolet.db", 'song'),
            'song_tag' => new Model("sqlite/MyMusicolet.db", 'song_tag'),
            'hist' => new Model("sqlite/MyMusicolet.db", 'hist'),
        ];
        $model = new Model("sqlite/MyMusicolet.db");
        $model->query("create table if not exists `song` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `filepath` TEXT NULL,
            `s10p_filepath` TEXT NULL,
            `title` VARCHAR(255) NULL,
            `album` VARCHAR(255) NULL,
            `album_artist` VARCHAR(255) NULL,
            `duration` INTEGER 0,
            `bitrate` INTEGER 0,
            `size` INTEGER 0
        )");
        $model->query("create table if not exists `song_tag` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `song_id` INTEGER,
            `tag` INTEGER
        )");
        $model->query("create table if not exists `hist` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `song_id` INTEGER,
            `playtime` INTEGER 0
        )");
    }


    function anaM3u($m3ufile = "all-20200109.m3u"/*, $playlistName = "全部"*/){
        $list = file($m3ufile);
        $playlist = [];
        foreach ($list as $k => $v) {
            $playIndex = ceil($k / 2);
            if ($k == 0) continue;
            if ($k % 2 == 1) { //歌曲标题和时长
                $dur = $name = null;
                if (preg_match('/^\s*#EXTINF\s*\:\s*(\d+),(.*)$/', trim("\r\n\s", $v), $matches)) {
                    list ( , $dur, $name) = $matches;
                }
                $playlist[$playIndex] += ['dur' => $dur ?: 0, 'name' => $name ?: $v];
            } else { //文件路径
                $playlist[$playIndex]['path'] = trim("\r\n\s", $v);
            }
        }
        return $playlist;
    }
    
    
    function req($id, $limit){    
        // $ret = $this->http->post('https://fdsfsd.com/abc.go', ['id' => $id, 'limit' => $limit], 55);
    }
    
    
    function import($tag, $m3ufile){
        $playlist = $this->anaM3u($m3ufile);
        $modelS = $this->sqlite['song'];
        foreach ($playlist as $v) {
            if (! $models->find(['s10p_filepath' => $v['path']])) {
                $data = [
                    'filepath' => '',//@todo 等待上传后得到公网链接
                    'title' => $v['name'],
                    'album' => '', //@todo getAlbum($v['path'])
                    'album_artist' => '', //@todo getAlbumArtist($v['path'])
                    'duration' => $v['dur'],
                    'bitrate' => '', //@todo getBitrate($v['path'])
                    'size' => filesize($v['path']),
                ];
                $modelS->insert($data);
                $this->log('import', print_r($data, 1));
            }
        }
    }
    
    
    function log($name, $content){
        @mkdir('log');
        file_put_contents("log/{$name}.log", $content."\n", FILE_APPEND);
    }
    
    
    function sql($sql){
        $ret = print_r($this->sqlite->query($sql), 1);
        $this->log('sql', "sql: {$sql}\nret: {$ret}");
        echo $ret;
    }
    
}

@$func = $_SERVER['argv'][1];
@$param2 = $_SERVER['argv'][2];

switch ($func) {
    case 'sql':
        $params = [$param2];
        break;
    case 'import':
        $params = [$param2];
        break;
    default:
        die('wtf');
}

if ($func) {    
    $finder = new M3uToTable();
    call_user_func_array([$finder, $func], $params);
}

//example: /usr/local/php/bin/php M3uToTable.php sql "select count(1),keywords from video where status != -9 and can_play=1 group by keywords"
//example: /usr/local/php/bin/php M3uToTable.php import "all-20200109.m3u"

//导出到HTML的操作：
//先执行: /usr/local/php/bin/php M3uToTable.php sql "select *, 'http://cloud.v.duowan.com/index.php?r=public/play&vid='||vid as backurl, 'http://video.duowan.com/play/'||vid||'.html' as playurl from video where status != -9" > video.export.20180712-1.txt
//在notepad++中用正则将链接替换成超链接标签
//  http\:\/\/video\.duowan\.com\/play\/\d+\.html  ->  <a target="_blank" href="$0">$0</a>
//  http\:\/\/cloud\.v\.duowan\.com\/index\.php\?r=public\/play&vid=\d+  ->  <a target="_blank" href="$0">$0</a>
