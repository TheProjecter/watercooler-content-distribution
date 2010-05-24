#!/usr/bin/python2.6

import sys
import MySQLdb

conn = MySQLdb.connect (host = "localhost",
                        user = "root",
                        passwd = "adminsql",
                        db = "watercooler")
cursor = conn.cursor ()

cursor.execute ("DROP TABLE IF EXISTS users;")
cursor.execute ("""
                CREATE TABLE users
                (
                        uid             int(16)         NOT NULL UNIQUE auto_increment,
                        username        varchar(32)     NOT NULL UNIQUE,
                        password        varchar(32)     NOT NULL,
                        first_name      varchar(32),
                        last_name       varchar(32),
                        phone_number    varchar(16)     NOT NULL UNIQUE,
                        email           varchar(32)     NOT NULL UNIQUE,
                        status          int(4)          NOT NULL,
                        cid             int(16)         NOT NULL,
                        PRIMARY KEY (uid),
                        FOREIGN KEY (cid) REFERENCES carriors
                );
                """)
cursor.execute ("DROP TABLE IF EXISTS receptions;")
cursor.execute ("""
                CREATE TABLE receptions
                (
                        uid             int(16)         NOT NULL,
                        rid             int(16)         NOT NULL,
                        FOREIGN KEY (uid) REFERENCES users,
                        FOREIGN KEY (rid) REFERENCES reception_methods
                );
                """)
cursor.execute ("DROP TABLE IF EXISTS reception_methods;")
cursor.execute ("""
                CREATE TABLE reception_methods
                (
                        rid             int(16)         NOT NULL UNIQUE auto_increment,
                        method_type     varchar(32)     NOT NULL,
                        PRIMARY KEY (rid)
                );
                """)
cursor.execute ("DROP TABLE IF EXISTS carriors;")
cursor.execute ("""
                CREATE TABLE carriors
                (
                        cid             int(16)         NOT NULL UNIQUE auto_increment,
                        carrior_name    varchar(32)     NOT NULL,
                        PRIMARY KEY (cid)
                );
                """)
cursor.execute ("DROP TABLE IF EXISTS favorites;")
cursor.execute ("""
                CREATE TABLE favorites
                (
                        uid             int(16)         NOT NULL,
                        sid             int(16)         NOT NULL,
                        priority        int(8)          NOT NULL,
                        FOREIGN KEY (uid) REFERENCES users,
                        FOREIGN KEY (sid) REFERENCES feed_sources
                );
                """)
cursor.execute ("DROP TABLE IF EXISTS feed_sources;")
cursor.execute ("""
                CREATE TABLE feed_sources
                (
                        sid             int(16)         NOT NULL UNIQUE auto_increment,
                        source_name     varchar(32)     NOT NULL,
                        source_url      varchar(256)    NOT NULL,
                        PRIMARY KEY (sid)
                );
                """)
cursor.execute ("DROP TABLE IF EXISTS feed_stories;")
cursor.execute ("""
                CREATE TABLE feed_stories
                (
                        fid             int(16)         NOT NULL UNIQUE auto_increment,
                        title           varchar(256)    NOT NULL,
                        content         varchar(256)    NOT NULL,
                        url             varchar(256)    NOT NULL,
                        time_stamp      int(16)         NOT NULL,
                        sid             int(16)         NOT NULL,
                        gid             int(16)         NOT NULL,
                        PRIMARY KEY (fid),
                        FOREIGN KEY (sid) REFERENCES feed_sources,
                        FOREIGN KEY (gid) REFERENCES feed_categories
                );
                """)
cursor.execute ("DROP TABLE IF EXISTS feed_categories;")
cursor.execute ("""
                CREATE TABLE feed_categories
                (
                        gid             int(16)         NOT NULL UNIQUE auto_increment,
                        category        varchar(32)     NOT NULL,
                        PRIMARY KEY (gid)
                );
                """)

conn.commit ()
cursor.close ()
conn.close ()
