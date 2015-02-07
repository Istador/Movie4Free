<?php

include("util.php");

//movies table
$db->exec("
drop table if exists donations;
drop table if exists files;
drop table if exists movies;

create table if not exists movies (
  m_id integer primary key autoincrement
, m_viewcost double not null default 0
, m_dlcost double not null default 0
, m_downloads integer not null default 0
, m_views integer not null default 0
, m_name text not null unique
, m_length text not null
, m_width integer not null
, m_height integer not null
);

create table if not exists files (
  f_id integer primary key autoincrement
, m_id integer not null
, f_name varchar(20) not null unique
, f_size integer not null
, f_mime varchar(30) not null
, f_type varchar(30) not null
, foreign key (m_id) references movies(m_id)
);

create table if not exists donations (
  d_id integer primary key autoincrement
, d_dtime datetime not null
, d_amount double not null
, d_donator text not null
, d_comment text
);

");

//Gefahrengebiete
$db->exec("insert into movies (m_id, m_name, m_length, m_viewcost, m_dlcost, m_width, m_height) values
  (1, 'Gefahrengebiete LD', '11:30', 0.008, 0.021, 480, 270)
, (2, 'Gefahrengebiete SD', '11:30', 0.010, 0.023, 960, 540)
, (3, 'Gefahrengebiete HD', '11:30', 0.012, 0.025, 1920, 1080)
");
$id = $db->lastInsertRowid();

//Dateien
$db->exec("insert into files (m_id, f_name, f_size, f_mime, f_type) values
  (1, 'LD.mp4', 246258062, 'video/mp4', 'video/mp4; codecs=avc1.42E01E,mp4a.40.2')
, (1, 'LD.webm', 123, 'video/webm', 'video/webm; codecs=vp8,vorbis')
, (2, 'SD.mp4', 844589882, 'video/mp4', 'video/mp4; codecs=avc1.42E01E,mp4a.40.2')
, (2, 'SD.webm', 12345, 'video/webm', 'video/webm; codecs=vp8,vorbis')
");

//Spenden
$db->exec("insert into donations (d_dtime, d_amount, d_donator, d_comment) values
  ('2015-02-07 05:27', 10.00, 'Robin Ladiges', 'initial balance')
");
