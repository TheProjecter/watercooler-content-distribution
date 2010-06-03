#!/usr/bin/python2.6

import sys
import MySQLdb

conn = MySQLdb.connect (host = "localhost",
                        user = "root",
                        passwd = "adminsql",
                        db = "watercooler")
cursor = conn.cursor ()

users_table =  [("lampcover", "lctemp", "lc", "tang", "650-804-0503", "lampcover@gmail.com", 1, 1)]

receptions_table = [(1, 1)]

reception_table = [("email"),
			("sms_text"),
			("sms_link")]

carrior_table = [("AT&T"),
			("T-Mobile"),
			("Verizon"),
			("Sprint")]

favorites_table = [(1, 1, 1)]

sources_table = [("espn", "http://sports.espn.go.com/espn/rss/news")]

feeds_table = [("2010 NBA Playoffs: LeBron James confident Cleveland Cavaliers can come back against Boston Celtics", "LeBron James isn't listening to the nationwide criticism of his listless Game 5 performance against Boston.", "http://sports.espn.go.com/nba/playoffs/2010/news/story?id=5183847&campaign=rss&source=ESPNHeadlines", 1273684800, 1, 1)
]

category_table = [("top_story"),
		  ("highest_rated"),
		  ("most_viewed")]

cursor.executemany ("""
		    INSERT INTO users (username, password, first_name, last_name, phone_number, email, status, cid)
		    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
		    """, users_table)

cursor.executemany ("""
		    INSERT INTO receptions (uid, rid)
		    VALUES (%s, %s)
		    """, receptions_table)

cursor.executemany ("""
		    INSERT INTO reception_methods (method_type)
		    VALUES (%s)
		    """, reception_table)

cursor.executemany ("""
		    INSERT INTO carriors (carrior_name)
		    VALUES (%s)
		    """, carrior_table)

cursor.executemany ("""
		    INSERT INTO favorites (uid, sid, priority)
		    VALUES (%s, %s, %s)
		    """, favorites_table)

cursor.executemany ("""
		    INSERT INTO feed_sources (source_name, source_url)
		    VALUES (%s, %s)
		    """, sources_table)

cursor.executemany ("""
		    INSERT INTO feed_stories (title, content, url, time_stamp, sid, gid)
		    VALUES (%s, %s, %s, %s, %s, %s)
		    """, feeds_table)

cursor.executemany ("""
		    INSERT INTO feed_categories (category)
		    VALUES (%s)
		    """, category_table)

cursor.close ()
conn.commit ()
conn.close ()

