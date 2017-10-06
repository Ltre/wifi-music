<?php

header('Content-type:text/html; charset=utf-8');
ob_start();
date_default_timezone_set('PRC');
session_start();

//递归方式（DFS)
function scan($dir){
    $map = [];
    preg_match('/\/$/', $dir) OR $dir .= '/';
    $dir = rtrim($dir, '/');
    if (is_dir($dir) && ($dh = opendir($dir))) {
        while (false !== ($file = readdir($dh))) {
            if (in_array($file, ['.', '..'])) continue;
            $path = "{$dir}/{$file}";
            if (is_dir($path)) {
                $map += scan($path);
            } else {
                if (preg_match('/\.(mp3|wma|wav|m4a|ape|flac|aac)$/', $path)) {
                    $k = sha1($path);
                    $map[$k] = [
                        'path' => $path,
                        'file' => $file,
                    ];
                }
            }
        }
    }
    return $map;
}

//非递归 (BFS)
function scan2($dir){
    $map = [];
    $queue = [];
    array_unshift($queue, $dir);
    while (! empty($queue)) {
        $e = array_pop($queue);
        $e = rtrim($e, '/');
        if (is_dir($e) && ($dh = opendir($e))) {
            while (false !== ($file = readdir($dh))) {
                if (in_array($file, ['.', '..'])) continue;
                $path = "{$e}/{$file}";
                if (is_dir($path)) {
                    array_unshift($queue, $path);
                } else {
                    if (preg_match('/\.(mp3|wma|wav|m4a|ape|flac|aac)$/', $path)) {
                        $k = sha1($path);
                        $map[$k] = [
                            'path' => $path,
                            'file' => $file,
                        ];
                    }
                }
            }
        }
    }
    return $map;
}

// $map = scan('D:/SERV_WORKSPACE/WebstormProjects/wifi-music/sdcard');
$map = scan2('/Users/Ltre/mydir/projects/wifi-music/sdcard');
file_put_contents('list.json', json_encode($map));
echo json_encode(['map' => $map, 'len' => count($map)]);