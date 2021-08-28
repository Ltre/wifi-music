#!/usr/local/php/bin/php
<?php

include 'hehe/config.php';
include 'hehe/lib.php';
include 'hehe/dwHttp.php';
include 'hehe/Model.php';

class M3uToTable {
    
    var $batch;
    var $http;
    var $model;
    var $sqlite;
    
    function __construct(){
        $this->http = new dwHttp;
        $this->initDB();
    }
    
    
    function initDB(){
        @mkdir('sqlite');
        $this->sqlite = [
            'song' => new Model("sqlite/MyMusicolet.db", 'song'),
            'song_tag' => new Model("sqlite/MyMusicolet.db", 'song_tag'),
            'hist' => new Model("sqlite/MyMusicolet.db", 'hist'),
        ];
        $this->model = new Model("sqlite/MyMusicolet.db", 'song');
        $this->model->query("create table if not exists song (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            filepath TEXT DEFAULT NULL,
            s10p_filepath TEXT DEFAULT NULL,
            title VARCHAR(255) DEFAULT NULL,
            album VARCHAR(255) DEFAULT NULL,
            album_artist VARCHAR(255) DEFAULT NULL,
            duration INTEGER DEFAULT 0,
            bitrate INTEGER DEFAULT 0,
            size INTEGER DEFAULT 0
        )");
        $this->model->query("create table if not exists `song_tag` (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            song_id INTEGER,
            tag INTEGER
        )");
        $this->model->query("create table if not exists `hist` (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            song_id INTEGER,
            playtime INTEGER DEFAULT 0
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
                if (preg_match('/^\s*#EXTINF\s*\:\s*(\d+),(.*)$/', trim($v, "\r\n\s"), $matches)) {
                    list ( , $dur, $name) = $matches;
                }
                $playlist[$playIndex] = ['dur' => $dur ?: 0, 'name' => $name ?: $v];
            } else { //文件路径
                $playlist[$playIndex]['path'] = trim($v, "\r\n\s");
            }
        }
        return $playlist;
    }
    
    
    function req($id, $limit){    
        // $ret = $this->http->post('https://fdsfsd.com/abc.go', ['id' => $id, 'limit' => $limit], 55);
    }
    
    
    // php M3uToTable.php import 'ALL' '../all-20200109.m3u'
    function import($tag, $m3ufile){
        $playlist = $this->anaM3u($m3ufile);
        $modelS = $this->sqlite['song'];
        foreach ($playlist as $v) {
            $cond = ['s10p_filepath' => $v['path']];
            $data = [
                'filepath' => '',//@todo 等待上传后得到公网链接
                'title' => $v['name'],
                'album' => '', //@todo getAlbum($v['path'])
                'album_artist' => '', //@todo getAlbumArtist($v['path'])
                'duration' => $v['dur'],
                'bitrate' => '', //@todo getBitrate($v['path'])
                'size' => @filesize($v['path']) ?: 0,
            ];
            if (! $modelS->find($cond)) {
                $modelS->insert($cond + $data);
            } else {
                $this->update($cond, $data);
            }
            $this->log('import', print_r($cond + $data, 1));
        }
    }
    
    
    function log($name, $content){
        @mkdir('log');
        file_put_contents("log/{$name}.log", $content."\n", FILE_APPEND);
    }
    
    
    function sql($sql){
        $ret = print_r($this->model->query($sql), 1);
        $this->log('sql', "sql: {$sql}\nret: {$ret}");
        echo $ret;
    }
    
}

@$func = $_SERVER['argv'][1];
@$param2 = $_SERVER['argv'][2];
@$param3 = $_SERVER['argv'][3];

switch ($func) {
    case 'sql':
        $params = [$param2];
        break;
    case 'import':
        $params = [$param2, $param3];
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
