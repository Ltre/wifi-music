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


