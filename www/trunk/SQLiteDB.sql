DROP TABLE IF EXISTS users;
CREATE TABLE users
(
uid INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
username TEXT NOT NULL UNIQUE,
password TEXT NOT NULL,
first_name TEXT,
last_name TEXT,
phone_number TEXT NOT NULL UNIQUE,
email TEXT NOT NULL UNIQUE,
status INTEGER NOT NULL,
cid INTEGER NOT NULL,
FOREIGN KEY (cid) REFERENCES reception_methods
);

DROP TABLE IF EXISTS receptions;
CREATE TABLE receptions
(
uid INTEGER NOT NULL,
rid INTEGER NOT NULL,
FOREIGN KEY (uid) REFERENCES users,
FOREIGN KEY (rid) REFERENCES reception_methods
);

DROP TABLE IF EXISTS reception_methods;
CREATE TABLE reception_methods
(
rid INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
method_type TEXT NOT NULL
);

DROP TABLE IF EXISTS carriors;
CREATE TABLE carriors
(
cid INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
carrior_name TEXT NOT NULL
);

DROP TABLE IF EXISTS favorites;
CREATE TABLE favorites
(
uid INTEGER NOT NULL,
sid INTEGER NOT NULL,
priority INTEGER NOT NULL,
FOREIGN KEY (uid) REFERENCES users,
FOREIGN KEY (sid) REFERENCES feed_sources
);

DROP TABLE IF EXISTS feed_sources;
CREATE TABLE feed_sources
(
sid INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
source_name TEXT NOT NULL,
source_url TEXT NOT NULL
);

DROP TABLE IF EXISTS feed_stories;
CREATE TABLE feed_stories
(
fid INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
title TEXT NOT NULL,
content TEXT NOT NULL,
url TEXT NOT NULL,
time_stamp INTEGER NOT NULL,
sid INTEGER NOT NULL,
gid INTEGER NOT NULL,
FOREIGN KEY (sid) REFERENCES feed_sources,
FOREIGN KEY (gid) REFERENCES feed_categories
);

DROP TABLE IF EXISTS feed_categories;
CREATE TABLE feed_categories
(
gid INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
category TEXT NOT NULL
);

INSERT INTO carriors (carrior_name) VALUES ('AT&T');
INSERT INTO carriors (carrior_name) VALUES ('T-Mobile');
INSERT INTO carriors (carrior_name) VALUES ('Verizon');
INSERT INTO carriors (carrior_name) VALUES ('Sprint');

INSERT INTO carriors (carrior_name) VALUES ('testcarrier');
INSERT INTO carriors (carrior_name) VALUES ('testcarrier2');
