/**
 * for sqlite
 */


CREATE TABLE `song` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `filepath` TEXT NULL,
    `s10p_filepath` TEXT NULL,
    `title` VARCHAR(255) NULL,
    `album` VARCHAR(255) NULL,
    `album_artist` VARCHAR(255) NULL,
    `duration` INTEGER 0,
    `bitrate` INTEGER 0,
    `size` INTEGER 0
);


CREATE TABLE `song_tag` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `song_id` INTEGER,
    `tag` INTEGER
);


CREATE TABLE `hist` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `song_id` INTEGER,
    `playtime` INTEGER 0
);












-- 以下是查询范例 --


-- 获取每首歌曲已打标签数： 
select song_id, count(1) as cnt from song_tag group by song_id -- HAVING cnt > 1
-- 获取至少有两个标签（1个ALL,1个别的）的歌曲：
select * from song, (select song_id, count(1) as cnt from song_tag group by song_id HAVING cnt > 1) AS tmptable where song.id = tmptable.song_id order by tmptable.cnt DESC
-- 获取只有ALL标签的歌曲（可以看作没打标签的）：
select * from song, song_tag where song.id = song_tag.song_id and song_tag.tag = 'ALL' 
-- 获取同时打了某些标签的歌曲：
select song.* from song, (select song_id, count(1) as cnt from song_tag where tag IN ('AlarmClock', 'Epic', 'S+', '某推荐', '至喜', '舒') group by song_id HAVING cnt > 1) as tmptable where song.id = tmptable.song_id