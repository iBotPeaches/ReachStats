<?php
/**
* Installation Schematic File
* Generated on Thu, 04 Dec 2008 16:39:43 +0000 GMT
*/
$TABLE[] = "CREATE TABLE reachstat (
	id 						mediumint(9) NOT NULL,
    gamertag				varchar(25) NOT NULL default '',
    data					text,
    rank					varchar(35) NOT NULL,
    rank_id					tinyint(2) NOT NULL,
    last_active				varchar(55) NOT NULL,
    kd_ratio				decimal(9,2) NOT NULL,
    games_played			mediumint(6) NOT NULL,
    games_won				mediumint(6) NOT NULL,
    games_lost				mediumint(6) NOT NULL,
	total_kills				mediumint(10) NOT NULL,
	total_deaths			mediumint(10) NOT NULL,
	total_betrayals			mediumint(6) NOT NULL,
	total_assists			mediumint(10) NOT NULL,
	service_tag				varchar(4) NOT NULL,
	daily_challenges		smallint(5) NOT NULL,
	weekly_challenges       smallint(5) NOT NULL,
	total_playtime			varchar(75) NOT NULL,
	total_medals			mediumint(10) NOT NULL,
	first_played			varchar(55) NOT NULL,
	armory_completion	    tinyint(3) NOT NULL,
	win_percent				tinyint(3) NOT NULL,
  	weapons 				text,
  	medals 					text,
  	commendations 			text,
    sigs 					text,
    settings				text,
    pts						int(11) NOT NULL,
    stat_date				int(16) NOT NULL,
    sig_date	  			int(16) NOT NULL,
    inactive				int(1) NOT NULL,
	ip_address				varchar(47) NOT NULL,
	PRIMARY KEY (id)
);";

$TABLE[] = "CREATE TABLE reach_leaderboard (
   member_id				mediumint(9) NOT NULL,
   gamertag					varchar(25) NOT NULL,
   rank_id					tinyint(2) NOT NULL,
   rank						varchar(35) NOT NULL,
   win_percent				decimal(5,2) NOT NULL,
   kd_ratio					decimal(9,2) NOT NULL,
   ApG						decimal(9,2) NOT NULL,
   BpG						decimal(9,2) NOT NULL,
   KpG						decimal(9,2) NOT NULL,
   DpG						decimal(9,2) NOT NULL,
   SpG						decimal(9,2) NOT NULL,
   MpG						decimal(9,2) NOT NULL,
   total_games				mediumint(9) NOT NULL,
   total_kills				mediumint(9) NOT NULL,
   total_assists			mediumint(9) NOT NULL,
   total_deaths				mediumint(9) NOT NULL,
   total_medals				mediumint(9) NOT NULL,
   chest_completion			tinyint(3) NOT NULL,
  PRIMARY KEY (member_id)
);";



?>