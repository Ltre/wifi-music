<?php
/**
 * save to sqlite
 * 
 * 相关资料：
 *      m3u - 维基百科
 *      https://zh.wikipedia.org/wiki/M3U
 *      m3u文件 - 百度百科
 *      https://baike.baidu.com/item/m3u%E6%96%87%E4%BB%B6/365977
 *      [翻译] M3U 和M3U8 详解_from wiki
 *      https://log.fyscu.com/index.php/archives/28/
 * 
 *      #EXTM3U	文件的头部，必须是文件的第一行。	
 *      #EXTINF	指示多媒体文件的信息，包括播放时间(秒)和标题。	
 *      #EXTINF:191,Artist Name - Track Title
 *      
 */

$file = "all-20200109.m3u";
$list = file($file);
foreach ($list as $k => $v) {
    if ($k == 0) continue;
    if ($k % 2 == 1) { //歌曲标题和时长

    } else { //文件路径

    }
}


//@todo: 建立起 文件路径与最终http上传路径的对应表
//@todo：将播放列表名称视为歌曲特性标签，保存到  歌曲Id-标签  对应表
//@todo：