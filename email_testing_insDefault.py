#!/usr/bin/python2.6

import sys
import MySQLdb

conn = MySQLdb.connect (host = "localhost",
                        user = "root",
                        passwd = "adminsql",
                        db = "watercooler")
cursor = conn.cursor ()

users_table =  [("timhsieh", "TIM", "tim", "hsieh", "909-802-8597", "timpeihunghsieh@gmail.com", 1, 1),
		("timm", "TIMMM", "timmm", "hsieh", "650-804-0502", "thsieh0606@yahoo.com", 1, 1),
		("simon", "LAMP", "lc", "tang", "650-804-0503", "lampcover@gmail.com", 1, 1),
		("simonn", "LAMPP", "lcc", "tangg", "650-804-0504", "simonsiumantang@yahoo.com.hk", 1, 1)]

receptions_table = [(1, 1),
		    (2, 1),
			(3, 1),
			(4, 1)]

reception_table = [("email"),
		   ("sms_text"),
		   ("sms_link")]

carrior_table = [("AT&T"),
		 ("T-Mobile"),
		 ("Verizon"),
		 ("Sprint")]

favorites_table = [(1, 1, 1),
		   (1, 2, 2),
		   (1, 3, 3),
		   (2, 1, 3),
                   (2, 2, 2),
                   (2, 3, 1),
		   (3, 1, 2),
                   (3, 2, 1),
                   (3, 3, 3)]

sources_table = [("cnn", "http://rss.cnn.com/rss/cnn_topstories.rss"),
		 ("yahoo", "http://rss.news.yahoo.com/rss/topstories"),
		 ("espn", "http://sports.espn.go.com/espn/rss/news")]

feeds_table = [
("Obama: Afghan war will worsen before it improves", "AP - The war in Afghanistan will get worse before it gets better, President Barack Obama warned on Wednesday, but he declared his plan to begin withdrawing U.S. forces next year remains on track.", "http://news.yahoo.com/s/ap/20100513/ap_on_go_pr_wh/us_us_afghanistan", 1273685400, 2, 1),
("Obama proposes larger oil cleanup fund", "BP is lowering a second oil containment box called top hat to plug an oil leak in the Gulf of Mexico.", "http://www.cnn.com/2010/US/05/12/oil.spill.main/index.html?eref=rss_topstories&utm_source=feedburner&utm_medium=feed&utm_campaign=Feed%3A+rss%2Fcnn_topstories+%28RSS%3A+Top+Stories%29", 1273686600, 1, 2),
("2010 NBA Playoffs: LeBron James confident Cleveland Cavaliers can come back against Boston Celtics", "LeBron James isn't listening to the nationwide criticism of his listless Game 5 performance against Boston.", "http://sports.espn.go.com/nba/playoffs/2010/news/story?id=5183847&campaign=rss&source=ESPNHeadlines", 1273684800, 3, 3)
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

