#!/usr/bin/python2.6

import sys
import MySQLdb

# parameters:
# my_feed_url = (string) feed entry url
# return:
# python list of tuples of strings of usernames of each user who needs this story
# [(username, email, phone, carrior, method), (username2, email2, phone2, carrior, method).....]
# phone: (string): 909-802-8597
# carrior: (string): AT&T, Verizon, T-Mobile, Sprint
# method: (string): email, sms_text, sms_link
def getUsersByStory ( my_feed_url ):
    conn = MySQLdb.connect (host = "localhost",
                        user = "root",
                        passwd = "adminsql",
                        db = "watercooler")

    cursor = conn.cursor ()
    cursor.execute ("""
                    SELECT username, email, phone_number, carrior_name, method_type
                    FROM users, favorites, feed_sources, feed_stories, carriors, reception_methods
                    WHERE users.uid = favorites.uid
                    AND users.cid = carriors.cid
                    AND users.rid = reception_methods.rid
                    AND feed_sources.sid = favorites.sid
                    AND feed_stories.sid = feed_sources.sid
                    AND feed_stories.url = %s
                    """, my_feed_url)

    retVal = cursor.fetchall () 
    cursor.close ()
    conn.close ()
    return retVal
