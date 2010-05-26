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

INSERT INTO carriors (carrior_name) VALUES ('testcarrier');
INSERT INTO carriors (carrior_name) VALUES ('testcarrier2');

INSERT INTO carriors (carrior_name) VALUES ('att');
INSERT INTO carriors (carrior_name) VALUES ('verizon');
