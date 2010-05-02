# This is a sample "driver" to use a module called feedparser to
# 	download, parse, and display RSS feed data.
# To use this program, install Python 2.6.4.
# Then go to http://code.google.com/p/feedparser/downloads/list
# 	download feedparser-4.1.zip
# Then, open a cmd shell, type: python setup.py install
# Then, you can use this program as usual.

"""
Version LOG
1.0:
Elementary testing version
2.0:
Able to read RSS and eliminate some HTML trash
3.0:
organize some code and improved debug-ability
Reordered license information
Week 3 Demo version
4.0: 
Able to read and process ATOM as well
4.1:
Organized and commented in functions
Week 4 Demo version
4.2:
Revised the content processing function,
Able to remove trash more robustly
4.3:
Leo testing edition
4.3.1_TEST:
Now get description before content for content field,
affecting ATOM feeds only
Also changed _ContentCutter
4.3.2_TEST:
Modularize more codes into helper functions
Organized the "myfeed" so that we can keep track which works and
  which does not work
4.4_TEST:
Leo modified _ContentCutter to ensure correctness
5.0:
RSS:
	Improve robustness against empty fields
	introduce logging
	make use of time function (converge all time into UNIX format)
	Introduce Stories (list of list) to behave as specified
5.1:
Fixed some confusing printout names
ATOM:
	Improve robustness against empty fields
	introduce logging
	make use of time function (converge all time into UNIX format)
	Introduce Stories (list of list) to behave as specified
5.2:
Fixed extra print statements
Implement subsequent ContentCutting functions to further process impurities
	1: remove extra whitespace (__Cutter2)
	2: remove all subsequent sentences if we find:
			multiple packed \n or \n seperated with (spaces or tabs) (__Cutter1)
	3: remove all words from end, up to a list of "whitelist" allowable ending
			if such ending is not detected, this "remover" does nothing (__Cutter1)

5.3: Future
Testing of above codes works or not
"""


# handle time stamps
import time

# define ending characters
def __CheckEnding(ending):
	LegalEndings = [']', '...', '.', '!', '?', '"', '\'', ':']
	Endings = set(LegalEndings)
	result = ending in Endings
	return result

# a function to remove duplicate whitespace in content
def __Cutter2(content):
	for index in (range((len(content))-1)):
		if ((content[index] == ' ') and (content[index+1] == ' ')):
			newcontent = content[:index+1] + content[index+2:]
			return __Cutter2(newcontent)
	return content

# a function to remove trash words at the end of content (2, 3)
# lemma: There is no useful information after this syntax: 'legal_ending' [space]* \n [space]* \n
def __Cutter1(content):
	last_legal_pos=0;
	endpos = 0;
	# find last legal ending position
	for index in range(len(content)):
		if (__CheckEnding(content[index])):
			last_legal_pos=index
	# last_legal_pos if == 0, means cant remove anything
	if (last_legal_pos != 0):
		endpos = 0
		end = len(content)-1
		current = last_legal_pos+1
		for index in range(current, end):
			if content[index] != ' ':
				current = index
				break
		# immediate end noticed, that means trailing whitespace only
		if (current == (last_legal_pos + 1)):
			return content[:current]

		# check if multiple \n following, only seperated by spaces if there is any
		#   then we conclude anything afterwards are trash (likely ADs)
		#   lemma: There is no useful information after this syntax: 'legal_ending' [space]* \n [space]* \n
		if ((content[current] == '\n') or (content[current] == '\r\n')):
			for index in range(current, end):
				if ((content[index+1] == '\n') or (content[index+1] == '\r\n')):
					endpos = index + 1
					break
				if ((content[index+1] != ' ') and (content[index+1] != '\n') and (content[index+1] != '\r\n')):
					endpos = 0
					break

	if (endpos != 0):
		return content[:endpos]
	else:
		return content
		

# a function to remove trash, specifically HTML codes for content
def __CutterHTML(content):
	flag = 0
	for index in range(len(content)):
		if (content[index] == '<'):
			start_pos = index
			flag = 1
		if (content[index] == '>'):
			end_pos = index
			#for index2 in range(index+1, len(content)):
			#	if (ord(content[index2])<33):
			#		end_pos=end_pos+1
			#	else:
			#		break
			break
			
	if (flag == 1): 		
		new_content = content[:start_pos] + content[end_pos+1:]
		return _ContentCutter(new_content)
	else:
		return content

# a overall, main function to link up all content processing functions
# this enable us to add more function without modifying many codes
def _ContentCutter(content):
	mycontent1 = __CutterHTML(content)
	mycontent2 = __Cutter1(mycontent1)
	mycontent3 = __Cutter2(mycontent2)
	return mycontent3
		
# a helper function to display global feed information
def _DisplayGlobal(myfeed, type):
	# calculate how many "entries" in the feed
	n_entries = len(myfeed['entries'])
	print 'There are' , n_entries , 'entries in the feed'

	# print details of the feeds (global):
	if myfeed.feed.has_key('title'):
		print 'Feed Title:' , myfeed.feed.title
	if myfeed.feed.has_key('link'):
		print 'Feed Link: ', myfeed.feed.link
	if (type == 'RSS'):
		if myfeed.feed.has_key('description'):
			print 'Feed Description: ', myfeed.feed.description
		if myfeed.feed.has_key('date'):
			print 'Feed Date: ', myfeed.feed.date
			print 'Feed Date (in list form): ', myfeed.feed.date_parsed
	elif (type == 'ATOM'):
		if (myfeed.feed.has_key('subtitle') and (len(myfeed.feed.subtitle) != 0)):
			print 'Feed Subtitle:' , myfeed.feed.subtitle
		if myfeed.feed.has_key('updated'):
			print 'Feed Date: ', myfeed.feed.updated
			print 'Feed Date (in list form): ', myfeed.feed.updated_parsed

	return

# a specialized function to parse and process RSS feeds
#	input: f = local file to write to
#		   log = local log for error
#		   myfeed = feedparser parsed object
#   output: List of stories

def _RSS(f, log, myfeed):
	# intialize the story list, note, for each entry, it is a list, and append
	#   the list into "stories"
	# story is [Feed Title, Entry Title, Entry Content, Entry Category, Entry URL, Entry Timestamp]
	stories = []

	# calculate how many "entries" in the feed
	n_entries = len(myfeed['entries'])

	# print details of the feeds (global):
	_DisplayGlobal(myfeed,'RSS')

	# get feed title
	feedtitle = 'Undefined'
	if myfeed.feed.has_key('title'):
		feedtitle = myfeed.feed.title

	# write all entries parsed on local file
	for count in range(n_entries):
		f.write('Entry ' + str(count+1) + ' Information:\n')
		f.write('Feed Title: ' + feedtitle + '\n')
		# get entry title
		entrytitle = 'Undefined'
		if myfeed.entries[count].has_key('title'):
			entrytitle = myfeed.entries[count].title
			f.write('Entry Title: '+ entrytitle + '\n')
		else:
			f.write('Entry Title: Undefined' + '\n')
			log.write('Entry ' + str(count+1) + ' error: Entry title = Undefined\n')

		# process content info (clear out HTML codes)
		content = myfeed.entries[count].description
		content = _ContentCutter(content)
		f.write('Content: ' + content + '\n')

		# get entry URL
		entryURL = ''
		if (myfeed.entries[count].has_key('link') and (len(myfeed.entries[count].link) != 0)):
			entryURL = myfeed.entries[count].link
		elif (myfeed.entries[count].has_key('id') and (len(myfeed.entries[count].id) != 0)):
			entryURL = myfeed.entries[count].id
		else:
			entryURL = 'localhost'
			log.write('Entry ' + str(count+1) + ' error: entryURL = localhost\n')

		f.write('Entry URL: ' + entryURL + '\n')

		# write date of entries
		# convert Universal Feed Parser generated time (tuple) into UNIX time

		UNIX_time = 0
		if myfeed.entries[count].has_key('date_parsed'):
			date_parsed = myfeed.entries[count].date_parsed
			UNIX_time = int(time.mktime(date_parsed))
			f.write('Time Stamp: ' + str(UNIX_time) + '\n')
			f.write('Time Stamp GMT DEBUG: ' + str(date_parsed[0]) + '/' + str(date_parsed[1]) + '/' + \
			str(date_parsed[2]) + ' ' + str(date_parsed[3]) + ':' + str(date_parsed[4]) + '\n\n')
		else:
			f.write('Time Stamp: 0\n\n')
			log.write('Entry ' + str(count+1) + ' error: Time Stamp = 0\n')

		# make a story from above parsed content
		# story is [Feed Title, Entry Title, Entry Content, Entry Category, Entry URL, Entry Timestamp]
		if content == '':
			content = 'Undefined'
		story = [feedtitle, entrytitle, content, 'NULL', entryURL, UNIX_time]
		stories.append(story)

	return stories


def _ATOM(f, log, myfeed):
	# intialize the story list, note, for each entry, it is a list, and append
	#   the list into "stories"
	# story is [Feed Title, Entry Title, Entry Content, Entry Category, Entry URL, Entry Timestamp]
	stories = []

	# calculate how many "entries" in the feed
	n_entries = len(myfeed['entries'])

	# print details of the feeds (global):
	_DisplayGlobal(myfeed,'ATOM')

	# get feed title
	feedtitle = 'Undefined'
	if myfeed.feed.has_key('title'):
		feedtitle = myfeed.feed.title

	# write all entries parsed on local file
	for count in range(n_entries):
		f.write('Entry ' + str(count+1) + ' Information:\n')
		f.write('Feed Title: ' + feedtitle + '\n')
		# get entry title
		entrytitle = 'Undefined'
		if myfeed.entries[count].has_key('title'):
			entrytitle = myfeed.entries[count].title
			f.write('Entry Title: '+ entrytitle + '\n')
		else:
			f.write('Entry Title: Undefined' + '\n')
			log.write('Entry ' + str(count+1) + ' error: Entry title = Undefined\n')


		#  ----------  Retrieve content info  ------------
		pos = 0
		# intialize content variable to empty string (very useful)
		content = ''

		# get content from description field first, if possible
		content = myfeed.entries[count].description

		# if the content is empty, try to get in content field (atom specific)
		if content == '':
			# when entries[count] has content, get the content out
			if myfeed.entries[count].has_key('content'):
				for content_index in range(len(myfeed.entries[count].content)):
					content = myfeed.entries[count].content[content_index].value
					if len(content) != 0:
						break

		# when content field also empty string, we cannot do anything more
		if content == '':
			log.write('Entry ' + str(count+1) + ' error: Content is empty\n')
		else:
			# ---------- Process content (clear out HTML codes) ---------------
			content = _ContentCutter(content)
			
		f.write('Content: ' + content + '\n')

		# get entry URL (ID first, before LINK)
		#   this order seems more correct in ATOM feeds
		entryURL = ''
		if (myfeed.entries[count].has_key('id') and (len(myfeed.entries[count].id) != 0)):
			entryURL = myfeed.entries[count].id
		elif (myfeed.entries[count].has_key('link') and (len(myfeed.entries[count].link) != 0)):
			entryURL = myfeed.entries[count].link
		else:
			entryURL = 'localhost'
			log.write('Entry ' + str(count+1) + ' error: entryURL = localhost\n')

		f.write('Entry URL: ' + entryURL + '\n')

		# write date of entries
		# convert Universal Feed Parser generated time (tuple) into UNIX time

		UNIX_time = 0
		
		if (myfeed.entries[count].has_key('updated') or  myfeed.entries[count].has_key('published')):
			if myfeed.entries[count].has_key('updated'):
				date_parsed = myfeed.entries[count].updated_parsed
			else:
				date_parsed = myfeed.entries[count].published_parsed

			UNIX_time = int(time.mktime(date_parsed))
			f.write('Time Stamp: ' + str(UNIX_time) + '\n')
			f.write('Time Stamp GMT DEBUG: ' + str(date_parsed[0]) + '/' + str(date_parsed[1]) + '/' + \
			str(date_parsed[2]) + ' ' + str(date_parsed[3]) + ':' + str(date_parsed[4]) + '\n\n')
		else:
			f.write('Time Stamp: 0\n\n')
			log.write('Entry ' + str(count+1) + ' error: Time Stamp = 0\n')

		# make a story from above parsed content
		# story is [Feed Title, Entry Title, Entry Content, Entry Category, Entry URL, Entry Timestamp]
		if content == '':
			content = 'Undefined'
		story = [feedtitle, entrytitle, content, 'NULL', entryURL, UNIX_time]
		stories.append(story)

	return stories



def main():

	# import the module
	import feedparser

	# handle unicode
	import codecs

	# create object myfeed, which stores information of parsed CNN top stories RSS
	# WORKING:
	# nicely regular RSS feed, easy to process HTML codes
	# myfeed = feedparser.parse('http://rss.cnn.com/rss/cnn_topstories.rss')

	# slightly more difficult RSS feed
	# myfeed = feedparser.parse('http://sports-ak.espn.go.com/espn/rss/news')

	# atom feed, strangely formatted, but it is working
	myfeed = feedparser.parse('http://feeds.nytimes.com/nyt/rss/HomePage')


	# NOT WORKING:
	# some ADs are considered blank entries, there are trash words that are not HTML
	# seems impossible to get rid of
	# The ADs problem can be solved by removing blank content entries
	# But we cannot do it now as it may hide bugs
	# myfeed = feedparser.parse('http://feeds.pheedo.com/toms_hardware_headlines')

	# Leo tried to remove all HTML trash already, but the formatting of output still wierd
	# myfeed = feedparser.parse('http://feeds.feedburner.com/caranddriver/blog')


	# create a local temp file that store all parsed content for demostration purpose
	# firstly, check for feeds encoding and synchronize this information
	# f = open("feeds.txt", "w")
	myfeed_encoding = myfeed.encoding
	f = codecs.open('feeds.txt', encoding=myfeed_encoding, mode='w')

	# create a local log for indicating error
	errlog = open("ERRORLOG.txt", mode ='w')

	# display global feed information that shared across all entries
	print 'Feed Encoding: ', myfeed_encoding
	print 'Feed version (type): ', myfeed.version

	# run specified parser corresponding to type of feeds (RSS,atom,others)
	if (myfeed.version[:3] == "rss"):
		print 'VERBOSE: RSS feed detected!'
		stories = _RSS(f, errlog, myfeed)
	elif (myfeed.version[:4] == "atom"):
		print 'VERBOSE: ATOM feed detected!'
		stories = _ATOM(f, errlog, myfeed)
	else:
		stories = []
		print 'UNKNOWN feed type!'

	f.close()

if __name__ == "__main__":
	main()


# Copyright information
"""
Authors: CS 130 Watercooler Content Distribution Engine
GPL V3
There is no warranty in using such source codes or programs.
There may be bugs, incorrect infomation, delays, etc, that may induce loss to
you. We are not held responsible in any way.


"""
"""
Copyright (c) 2002-2005, Mark Pilgrim
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice,
  this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice,
  this list of conditions and the following disclaimer in the documentation
  and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS'
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
"""
