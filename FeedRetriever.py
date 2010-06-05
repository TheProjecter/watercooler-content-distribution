#!/usr/bin/python2.6

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
	1: remove extra whitespace (__DuplicateSpace)
	2: remove all subsequent sentences if we find:
			multiple packed \n or \n seperated with (spaces or tabs) (__AdsFilter)
	3: remove all words from end, up to a list of "whitelist" allowable ending
			if such ending is not detected, this "remover" does nothing (__AdsFilter)

5.3:
Testing of above codes works or not
Fixed bug by removing ':' as LegalEndings
Fixed bug of __AdsFilter on ending with 'H.264' using regular expression
Currently __AdsFilter does nothing towards non-ascii texts, and we do not aim to process those
Fixed bug of possibility of trailing whitespace and newlines (eg space after newline), which would escape __AdsFilter check

5.3.1:
Category testing included, most RSS feeds have no such information
more testing feeds added

5.4
replace '&lt;' '&gt;' unicode phrases. This is essential for __HTMLCutter to work
replace '&nbsp;' phrases. This seems to be the only HTML leftovers
Fixed possibility of RSS feed entries to have time stamp 0 (this case we use feed's time stamp)

5.4.1
Modularize to work with driver script

5.4.2
Added more simple filters (remove leading whitespace and \n)

6.0 Test
Included database codes, given by Ricky
ALL PRELIMINARY CODINGS DONE
Tests pending (will be done soon afterwards)

6.0.1 Test
Fixed some syntax errors
From now on, testing is done on server, using python 2.6.5
Code freeze for Friday discussion for concensus

6.0.2 Test
Fixing feed title name matching, algorithm flaws
Fix to a point ON DUPLICATE KEY UPDATE c=c+1;
code freeze until solution found

6.1 Beta
No "compile" error
More testings need to be done on more feeds

6.1.1 Beta
fixed some minor errors
default debug flag is false, will not print unless exceptional case
Only one line feed title is displayed for each feed!

6.1.2 Beta
fixed some minor errors (;)

6.1.3 Beta
Realized more bugs on unicode, fixed (&mdash;)
Fixed some more index out of bound issue

6.1.4 Beta Tested
Tested with email server, emails successfully sent

6.2 Test
Improving efficiency: checking time stamp before processing content
	avoiding need to remove extra stories later also
Corrected some more stupid serious bugs (ContentCutter)

6.3 Test
Add another Filter to filter away difficult-to-remove advertisements
	currently, most advertisements are deleted, all unit test case give "perfect" result

6.3.1 Test
Defaultly converted to non unicode before storing to database for united format

6.3.2 Test
Further added more unicode conversion functions

6.3.3 Test
Modified CutterHTML to handle wierd case (mismatching number of < >)
reverted 6.3.1 change, as it may cause faulting error (crashes)

6.3.4 Test
Researched unicode again, trying another method to robustly convert
all unicode to ascii for database "insert" and email servers, to avoid crashing
basically, its "ignoring" unconvertible char, rather than raising exception

6.3.5 Test
Verified stories output identical to 6.1.4
Optimized the logic of __AdsFilter and __AdvAdsFilter
This is used for performance test

6.3.6 Test
Added missing thing (from local version)
Adding additional checks to avoid crashing due to these URLS
	- Valid URL but not a feed ('www.yahoo.com')
	- Invalid URL ('iaminvalid')
	- empty URL ('')

6.4 Beta
Tested with Tim's Email server and totally 46 emails and texts sent
All received as intended

6.5 Bug test
Fixing the duplicate email bug
added tons of print messages in default mode, to be removed later
identified bug reason, hypothesis made

6.5.1 Test
replaced SQL code in UpdateFeed()
Added logic not to add existing entry

6.6 Test
Appearing seemed to work, now run it in server for a day to see
Modifying driver also to cooperate

6.6.1 Test
Added even more unicode conversion as bugs found

6.6.2 Test
Make debug log look nicer
Try to resolve bugs that title cant match in some rare scenarios

6.6.3 Test
Changed title to be normalized from unicode, avoid failure to match in rare case
changing story definition once again to contain more information
# old story is [Feed Title, Entry Title, Entry Content, Entry Category, Entry URL, Entry Timestamp]
# new story is [Feed Title, Feed URL, Entry Title, Entry Content, Entry Category, Entry URL, Entry Timestamp]
This will not affect email server in anyway

6.6.4 Test
changed empty content "default" to nicer version as Matt suggested

6.6.5 Beta
The 6.6.4 is tested, giving intended behaviour.
*** Starting new branch below ***

6.7 Test
Change "story" is per feed specific, to facilitate improvement reducing on amount of email spent:
[Feed Title, Feed URL, [Entry Titles], [Entry Contents], [Entry Categories], [Entry URLs], [Entry Timestamps]]
added more comments to make codes more readable

6.8 Test
Fixed duplication email bug

7.0 Beta
Unless more bugs are found, this is the version for public demo

7.0.1 Beta
Put length limit of title to 255 also (in addition to content)

------ CODE FREEZE UNTIL BUGS FOUND -------
------ USE 7.0 TO TEST! -----------------

Future:
add threads to parallelize processing data when a list of URL is obtained

"""


# handle time stamps
import time

# handle regular expression
import re

# import the module
import feedparser

# handle unicode
import codecs
import unicodedata

# handle database
import sys
import MySQLdb


# define ending characters, and check it
# return true if the char is an element of LegalEndings, false otherwise
# verified logic
def __CheckEnding(ending):
	LegalEndings = [']', '...', '.', '!', '?', '"', '\'']
	Endings = set(LegalEndings)
	# is 'ending' in the set? Ture/False
	result = ending in Endings
	return result


# a function to replace corresponding unicode to '<' and '>', for __CutterHTML to work
# verified logic
def __PreHTMLUnicode(content):
	for index in (range((len(content))-3)):
		ending = index+4
		if (content[index:ending] == '&lt;'):
			newcontent = content[:index] + '<' + content[ending:]
			return __PreHTMLUnicode(newcontent)
		if (content[index:ending] == '&gt;'):
			newcontent = content[:index] + '>' + content[ending:]
			return __PreHTMLUnicode(newcontent)
	return content


# functions to replace corresponding unicode to ascii value
# they are necessarily to help the python library, improving "visual correctness"
# verified logic
def __ProHTMLUnicodeSpace(content):
	for index in (range((len(content))-5)):
		ending = index+6
		if (content[index:ending] == '&nbsp;'):
			newcontent = content[:index] + ' ' + content[ending:]
			return __ProHTMLUnicodeSpace(newcontent)
		if (content[index:ending] == '&#149;'):
			newcontent = content[:index] + '.' + content[ending:]
			return __ProHTMLUnicodeSpace(newcontent)
	return content

def __ProHTMLUnicodeDash(content):
	for index in (range((len(content))-6)):
		ending = index+7
		if (content[index:ending] == '&mdash;'):
			newcontent = content[:index] + '--' + content[ending:]
			return __ProHTMLUnicodeDash(newcontent)
		if (content[index:ending] == '&#8217;'):
			newcontent = content[:index] + '\'' + content[ending:]
			return __ProHTMLUnicodeDash(newcontent)
		if (content[index:ending] == '&#8211;'):
			newcontent = content[:index] + '-' + content[ending:]
			return __ProHTMLUnicodeDash(newcontent)
		if (content[index:ending] == '&#8212;'):
			newcontent = content[:index] + '--' + content[ending:]
			return __ProHTMLUnicodeDash(newcontent)
		if ((content[index:ending] == '&#8221;') or (content[index:ending] == '&#8220;')):
			newcontent = content[:index] + '"' + content[ending:]
			return __ProHTMLUnicodeDash(newcontent)
		if (content[index:ending] == '&#8226;'):
			newcontent = content[:index] + '.' + content[ending:]
			return __ProHTMLUnicodeDash(newcontent)
	return content


# a function to remove duplicate whitespace in content
# verified logic
def __DuplicateSpace(content):
	for index in (range((len(content))-1)):
		ending = index+2
		if (content[index:ending] == '  '):
			newcontent = content[:index+1] + content[ending:]
			return __DuplicateSpace(newcontent)
	return content


# remove trailing space and newlines
def __TrailingSpace(content):
	last = len(content)-1
	if (last >= 0):
		if ((content[last] == '\n') or (content[last] == ' ')):
			return __TrailingSpace(content[:last])
		else:
			return content
	else:
		return content


# remove leading space and newlines
def __LeadingSpace(content):
	length = len(content)
	if (length > 0):
		if ((content[0] == '\n') or (content[0] == ' ')):
			return __LeadingSpace(content[1:])
		else:
			return content
	else:
		return content


# a function to remove trash words at the end of content
# lemma: There is no useful information after this syntax: 'legal_ending' [space]* \n [space]* \n
def __AdsFilter(content):
	# define CHARS_SET
	CHARS_SET = re.compile(r'[a-zA-Z0-9]')
	last_legal_pos = 0
	endpos = 0
	# find last legal ending position
	for index in range(len(content)):
		if (__CheckEnding(content[index])):
			last_legal_pos=index
	# last_legal_pos if == 0, means cant remove anything
	if (last_legal_pos != 0):
		endpos = 0
		end = len(content)
		current = last_legal_pos + 1
		newcurr = 0
		# detect non-space position, from legal_ending_position to end
		# checking this: is it all space?
		for index in range(current, end):
			if content[index] != ' ':
				newcurr = index
				break
		# immediate end noticed, that means trailing whitespace only
		# newcurr == 0 since it is never assigned with 'index' above
		if (newcurr == 0):
			return content[:current]

		# reaching here means found non-space chars (potentially trash)
		# setup the "range" properly..., start from last_legal_pos + 1
		newcurr = current
		# extra code to proceed some special scenario (eg H.264) (basically preserve more characters)
		# effect: stop at the position when it can no longer find chars or digits after '.' eg
		for index in range (newcurr, end):
			if  (not (bool(CHARS_SET.search(content[index])))):
				current = index
				break
		# now skip some space if there is any, set "current" to pos of "first non-space"
		newcurr = current
		for index in range (newcurr, end):
			if content[index] != ' ':
				current = index
				break

		# check if multiple \n following, only seperated by spaces if there is any
		#   then we conclude anything afterwards are trash (likely ADs)
		#   lemma: There is no useful information after this syntax: 'legal_ending' [space]* \n [space]* \n
		if (content[current] == '\n'):
			current = current + 1
			for index in range(current, end):
				if (content[index] == '\n'):
					endpos = index + 1
					break
				if ((content[index] != ' ') and (content[index] != '\n')):
					endpos = 0
					break
	# do we really able to kill crap things? (checking endpos)
	if (endpos != 0):
		return content[:endpos]
	else:
		return content



# an advanced function to remove more trash content
# lemma: There is no useful information after former legal ending syntax,
#        when there are two legal ending syntax,
#        and, the previous one is immediately seperated by following syntax:
#        '2nd last legal ending' [space]* \n [space]* \n [space]* \n [space]* \n
def __AdvAdsFilter(content):
	legal_pos = 0
	previous_legal_pos = 0
	nl_count = 0
	
	# find last legal ending position
	for index in range(len(content)):
		if (__CheckEnding(content[index])):
			previous_legal_pos = legal_pos
			legal_pos = index

	endpos = 0
	# legal_pos if == 0 or previous_legal_pos == 0, means cant remove anything
	if ((previous_legal_pos != 0) and (legal_pos != 0)):
		# check previous_legal_pos follows by the defined lemma condtions....
		end = len(content)
		current = previous_legal_pos+1
		newcurr = current
		for index in range(newcurr, end):
			if (content[index] == '\n'):
				nl_count = nl_count + 1
			if ((content[index] != '\n') and (content[index] != ' ')):
				break
			if (nl_count == 4):
				endpos = current + 1
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
			if (flag == 0):
				newcontent = content[:index] + ' ' + content[index+1:]
				return __CutterHTML(newcontent)
			else:
				end_pos = index
				break

	if (flag == 1): 		
		new_content = content[:start_pos] + ' ' + content[end_pos+1:]
		return __CutterHTML(new_content)
	else:
		return content

# a overall, main function to link up all content processing functions
# this enable us to add more function (filters) without modifying many codes
def _ContentCutter(content):
	mycontent0 = __PreHTMLUnicode(content)
	mycontent1 = __CutterHTML(mycontent0)
	mycontent2 = __AdsFilter(mycontent1)
	mycontent2n = __AdvAdsFilter(mycontent2)
	mycontent3 = __DuplicateSpace(mycontent2n)
	mycontent4 = __TrailingSpace(mycontent3)
	mycontent5 = __ProHTMLUnicodeSpace(mycontent4)
	mycontent6 = __ProHTMLUnicodeDash(mycontent5)
	mycontent7 = __LeadingSpace(mycontent6)
	return mycontent7

def _ContentCutterD(content):
	# a debugging content cutter to print intermediate value
	"""
	debuglc = codecs.open('debuglc.txt', encoding='utf-8', mode='w')
	debuglc.write('------\n')
	debuglc.write(content + '\n')
	debuglc.write('------\n')
	mycontent0 = __PreHTMLUnicode(content)
	debuglc.write('000000\n')
	debuglc.write(mycontent0 + '\n')
	debuglc.write('000000\n')
	mycontent1 = __CutterHTML(mycontent0)
	debuglc.write('111111\n')
	debuglc.write(mycontent1 + '\n')
	debuglc.write('111111\n')
	mycontent2 = __AdsFilter(mycontent1)
	debuglc.write('222222\n')
	debuglc.write(mycontent2 + '\n')
	debuglc.write('222222\n')
	mycontent2n = __AdvAdsFilter(mycontent2)
	debuglc.write('2n2n2n\n')
	debuglc.write(mycontent2n + '\n')
	debuglc.write('2n2n2n\n')
	mycontent3 = __DuplicateSpace(mycontent2n)
	debuglc.write('333333\n')
	debuglc.write(mycontent3 + '\n')
	debuglc.write('333333\n')
	mycontent4 = __TrailingSpace(mycontent3)
	debuglc.write('444444\n')
	debuglc.write(mycontent4 + '\n')
	debuglc.write('444444\n')
	mycontent5 = __ProHTMLUnicodeSpace(mycontent4)
	debuglc.write('555555\n')
	debuglc.write(mycontent5 + '\n')
	debuglc.write('555555\n')
	mycontent6 = __ProHTMLUnicodeDash(mycontent5)
	debuglc.write('666666\n')
	debuglc.write(mycontent6 + '\n')
	debuglc.write('666666\n')
	mycontent7 = __LeadingSpace(mycontent6)
	debuglc.write('777777\n')
	debuglc.write(mycontent7 + '\n')
	debuglc.write('777777\n')
	"""
	print '------'
	print content
	print '------'
	mycontent0 = __PreHTMLUnicode(content)
	print '000000'
	print mycontent0
	print '000000'
	mycontent1 = __CutterHTML(mycontent0)
	print '111111'
	print mycontent1
	print '111111'
	mycontent2 = __AdsFilter(mycontent1)
	print '222222'
	print mycontent2
	print '222222'
	mycontent2n = __AdvAdsFilter(mycontent2)
	print '2n2n2n'
	print mycontent2n
	print '2n2n2n'
	mycontent3 = __DuplicateSpace(mycontent2n)
	print '333333'
	print mycontent3
	print '333333'
	mycontent4 = __TrailingSpace(mycontent3)
	print '444444'
	print mycontent4
	print '444444'
	mycontent5 = __ProHTMLUnicodeSpace(mycontent4)
	print '555555'
	print mycontent5
	print '555555'
	mycontent6 = __ProHTMLUnicodeDash(mycontent5)
	print '666666'
	print mycontent6
	print '666666'
	mycontent7 = __LeadingSpace(mycontent6)
	print '777777'
	print mycontent7
	print '777777'
	
	return mycontent7

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
		#if myfeed.feed.has_key('description'):
		#	print 'Feed Description: ', myfeed.feed.description
		if myfeed.feed.has_key('date'):
			print 'Feed Date: ', myfeed.feed.date
			# print 'Feed Date (in list form): ', myfeed.feed.date_parsed
	elif (type == 'ATOM'):
		#if (myfeed.feed.has_key('subtitle') and (len(myfeed.feed.subtitle) != 0)):
		#	print 'Feed Subtitle:' , myfeed.feed.subtitle
		if myfeed.feed.has_key('updated'):
			print 'Feed Date: ', myfeed.feed.updated
			# print 'Feed Date (in list form): ', myfeed.feed.updated_parsed
	if myfeed.feed.has_key('categories'):
		print 'Feed Categories:' ,myfeed.feed.categories
	else:
		print 'This feed does not contain category information'

	return


# a specialized function to parse and process RSS feeds
#	input: f = local file to write to
#		   log = local log for error
#		   myfeed = feedparser parsed object
#          latest_ts = latest time stamp, of all feeds in the database
#          feedurl = the URL of the feed
#   output: A story, containing all new entries, as:
#           [Feed Title, Feed URL, [Entry Titles], [Entry Contents], [Entry Categories], [Entry URLs], [Entry Timestamps]]
#           Note, for no new entries, the output will be [] AND NOT:
#           [Feed Title, Feed URL, [], [], [], [], []]

def _RSS(f, log, myfeed, latest_ts, feedurl, debug):
	# intialize the story, and corresponding entries lists
	# story is [Feed Title, Feed URL, [Entry Titles], [Entry Contents], [Entry Categories], [Entry URLs], [Entry Timestamps]]
	story = []
	entrytitle_list = []
	entrycontent_list = []
	entrycategory_list = []
	entryURL_list = []
	entry_ts_list = []

	if ((myfeed is None) or (myfeed.feed is None)):
		return story

	# calculate how many "entries" in the feed
	n_entries = len(myfeed['entries'])

	# print details of the feeds (global):
	if (debug):
		_DisplayGlobal(myfeed,'RSS')

	# get feed title
	feedtitle = 'Undefined'
	if myfeed.feed.has_key('title'):
		feedtitle = myfeed.feed.title
		feedtitle = unicodedata.normalize('NFKD', feedtitle).encode('ascii','ignore')
		feedtitle = feedtitle[:255]

	print 'Feed Title:' , feedtitle

	print 'LC DEBUG LATEST_TS IN RSS', str(latest_ts)
	# write all entries parsed on local file
	for count in range(n_entries):
		# check time_stamp of the entry before proceeding
		#   to avoid processing something we will trash!
		# convert Universal Feed Parser generated time (tuple) into UNIX time
		UNIX_time = 0
		if myfeed.entries[count].has_key('date_parsed'):
			date_parsed = myfeed.entries[count].date_parsed
			UNIX_time = int(time.mktime(date_parsed))
		elif myfeed.feed.has_key('date'):
			date_parsed = myfeed.feed.date_parsed
			UNIX_time = int(time.mktime(date_parsed))

		if ((UNIX_time == 0) or (UNIX_time > latest_ts)):
			if (debug):
				f.write('Entry ' + str(count+1) + ' Information:\n')
				f.write('Feed Title: ' + feedtitle + '\n')
			# get entry title
			entrytitle = 'Undefined'
			if myfeed.entries[count].has_key('title'):
				entrytitle = myfeed.entries[count].title
				entrytitle = unicodedata.normalize('NFKD', entrytitle).encode('ascii','ignore')

				if (debug):
					f.write('Entry Title: '+ entrytitle + '\n')
			else:
				if (debug):
					f.write('Entry Title: Undefined' + '\n')
				log.write('Entry ' + str(count+1) + ' error: Entry title = Undefined\n')

			entrytitle_list.append(entrytitle)

			# process content info (clear out HTML codes)
			content = myfeed.entries[count].description

			content = _ContentCutter(content)
			content = unicodedata.normalize('NFKD', content).encode('ascii','ignore')
			if (debug):
				f.write('Content: ' + content + '\n')

			# get entry URL
			entryURL = ''
			if (myfeed.entries[count].has_key('link') and (len(myfeed.entries[count].link) != 0)):
				entryURL = myfeed.entries[count].link
				entryURL = unicodedata.normalize('NFKD', entryURL).encode('ascii','ignore')
			elif (myfeed.entries[count].has_key('id') and (len(myfeed.entries[count].id) != 0)):
				entryURL = myfeed.entries[count].id
				entryURL = unicodedata.normalize('NFKD', entryURL).encode('ascii','ignore')
			else:
				entryURL = 'localhost'
				log.write('Entry ' + str(count+1) + ' error: entryURL = localhost\n')

			if (debug):
				f.write('Entry URL: ' + entryURL + '\n')

			entryURL_list.append(entryURL)

			# write date of entries
			if (debug):
				f.write('Time Stamp: ' + str(UNIX_time) + '\n')
				if (UNIX_time != 0):
					f.write('Time Stamp GMT DEBUG: ' + str(date_parsed[0]) + '/' + str(date_parsed[1]) + '/' + \
					str(date_parsed[2]) + ' ' + str(date_parsed[3]) + ':' + str(date_parsed[4]) + '\n\n')
				else:
					f.write('Time Stamp: 0\n\n')
			if (UNIX_time == 0):
				log.write('Entry ' + str(count+1) + ' error: Time Stamp = 0\n')

			entry_ts_list.append(UNIX_time)

			# make a story from above parsed content
			# story is [Feed Title, Feed URL, [Entry Titles], [Entry Contents], [Entry Categories], [Entry URLs], [Entry Timestamps]]
			if content == '':
				content = 'Feed brought to you by the Watercooler'
			entrycontent_list.append(content)
			entrycategory_list.append('Undefined')
	# end for ---------------------------------------------------------------------------------------------------------
	if (len(entry_ts_list) > 0):
		story = [feedtitle, feedurl, entrytitle_list, entrycontent_list, entrycategory_list, entryURL_list, entry_ts_list]

	# CONSISTENCY CHECK
	con1 = len(entrytitle_list)
	con2 = len(entrycontent_list)
	con3 = len(entrycategory_list)
	con4 = len(entryURL_list)
	con5 = len(entry_ts_list)
	if ((con1 != con2) or (con1 != con3) or (con1 != con4) or (con1 != con5)):
		print 'CONSISTENCY CHECK FAILED IN RSS STORY '
		print 'STORY IS:'
		print str(story)
		print '\n'

	return story


# a specialized function to parse and process ATOM feeds
#	input: f = local file to write to
#		   log = local log for error
#		   myfeed = feedparser parsed object
#          latest_ts = latest time stamp, of all feeds in the database
#          feedurl = the URL of the feed
#   output: A story, containing all new entries, as:
#           [Feed Title, Feed URL, [Entry Titles], [Entry Contents], [Entry Categories], [Entry URLs], [Entry Timestamps]]
#           Note, for no new entries, the output will be [] AND NOT:
#           [Feed Title, Feed URL, [], [], [], [], []]

def _ATOM(f, log, myfeed, latest_ts, feedurl, debug):
	# intialize the story, and corresponding entries lists
	# story is [Feed Title, Feed URL, [Entry Titles], [Entry Contents], [Entry Categories], [Entry URLs], [Entry Timestamps]]
	story = []
	entrytitle_list = []
	entrycontent_list = []
	entrycategory_list = []
	entryURL_list = []
	entry_ts_list = []

	if ((myfeed is None) or (myfeed.feed is None)):
		return story

	# calculate how many "entries" in the feed
	n_entries = len(myfeed['entries'])

	# print details of the feeds (global):
	if (debug):
		_DisplayGlobal(myfeed,'ATOM')

	# get feed title
	feedtitle = 'Undefined'
	if myfeed.feed.has_key('title'):
		feedtitle = myfeed.feed.title
		feedtitle = unicodedata.normalize('NFKD', feedtitle).encode('ascii','ignore')
		feedtitle = feedtitle[:255]
	print 'Feed Title:' , feedtitle

	print 'LC DEBUG LATEST_TS IN ATOM', str(latest_ts)
	# write all entries parsed on local file
	for count in range(n_entries):
		# check time_stamp of the entry before proceeding
		#   to avoid processing something we will trash!
		# convert Universal Feed Parser generated time (tuple) into UNIX time
		UNIX_time = 0
		if myfeed.entries[count].has_key('updated'):
			date_parsed = myfeed.entries[count].updated_parsed
			UNIX_time = int(time.mktime(date_parsed))
		elif myfeed.entries[count].has_key('published'):
			date_parsed = myfeed.entries[count].published_parsed
			UNIX_time = int(time.mktime(date_parsed))


		if ((UNIX_time == 0) or (UNIX_time > latest_ts)):
			if (debug):
				f.write('Entry ' + str(count+1) + ' Information:\n')
				f.write('Feed Title: ' + feedtitle + '\n')
			# get entry title
			entrytitle = 'Undefined'
			if myfeed.entries[count].has_key('title'):
				entrytitle = myfeed.entries[count].title
				entrytitle = unicodedata.normalize('NFKD', entrytitle).encode('ascii','ignore')
				if (debug):
					f.write('Entry Title: '+ entrytitle + '\n')
			else:
				if (debug):
					f.write('Entry Title: Undefined' + '\n')
				log.write('Entry ' + str(count+1) + ' error: Entry title = Undefined\n')

			entrytitle_list.append(entrytitle)

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
				if (False):
					content = _ContentCutterD(content)
				else:
					content = _ContentCutter(content) # this is original required

			content = unicodedata.normalize('NFKD', content).encode('ascii','ignore')
			if (debug):
				f.write('Content: ' + content + '\n')


			# get entry URL (ID first, before LINK)
			#   this order seems more correct in ATOM feeds
			entryURL = ''
			if (myfeed.entries[count].has_key('id') and (len(myfeed.entries[count].id) != 0)):
				entryURL = myfeed.entries[count].id
				entryURL = unicodedata.normalize('NFKD', entryURL).encode('ascii','ignore')
			elif (myfeed.entries[count].has_key('link') and (len(myfeed.entries[count].link) != 0)):
				entryURL = myfeed.entries[count].link
				entryURL = unicodedata.normalize('NFKD', entryURL).encode('ascii','ignore')
			else:
				entryURL = 'localhost'
				log.write('Entry ' + str(count+1) + ' error: entryURL = localhost\n')

			if (debug):
				f.write('Entry URL: ' + entryURL + '\n')

			entryURL_list.append(entryURL)

			# write date of entries
			if (debug):
				f.write('Time Stamp: ' + str(UNIX_time) + '\n')
				f.write('Time Stamp GMT DEBUG: ' + str(date_parsed[0]) + '/' + str(date_parsed[1]) + '/' + \
				str(date_parsed[2]) + ' ' + str(date_parsed[3]) + ':' + str(date_parsed[4]) + '\n\n')

			if (UNIX_time == 0):
				if (debug):
					f.write('Time Stamp: 0\n\n')
				log.write('Entry ' + str(count+1) + ' error: Time Stamp = 0\n')

			entry_ts_list.append(UNIX_time)

			# make a story from above parsed content
			# story is [Feed Title, Feed URL, [Entry Titles], [Entry Contents], [Entry Categories], [Entry URLs], [Entry Timestamps]]
			if content == '':
				content = 'Feed brought to you by the Watercooler'
			entrycontent_list.append(content)
			entrycategory_list.append('Undefined')
	# end for ---------------------------------------------------------------------------------------------------------
	if (len(entry_ts_list) > 0):
		story = [feedtitle, feedurl, entrytitle_list, entrycontent_list, entrycategory_list, entryURL_list, entry_ts_list]

	# CONSISTENCY CHECK
	con1 = len(entrytitle_list)
	con2 = len(entrycontent_list)
	con3 = len(entrycategory_list)
	con4 = len(entryURL_list)
	con5 = len(entry_ts_list)
	if ((con1 != con2) or (con1 != con3) or (con1 != con4) or (con1 != con5)):
		print 'CONSISTENCY CHECK FAILED IN ATOM STORY '
		print 'STORY IS:'
		print str(story)
		print '\n'

	return story


# This is deprecated UpdateFeed, only useful to test output of ONE URL
# THIS IS LOCAL TEST, NO modification to database is performed
def UpdateFeed_tester():
	# create object myfeed, which stores information of parsed CNN top stories RSS
	# WORKING Flawlessly:
	# nicely regular RSS feed, easy to process HTML codes (5.3 Verified)
	myfeed_all = []
	myfeed = feedparser.parse('cnn_topstories.rss')
	myfeed_all.append(myfeed)
	myfeed = feedparser.parse('cnn_world.rss')
	myfeed_all.append(myfeed)
	myfeed = feedparser.parse('ESPN_com.xml')
	myfeed_all.append(myfeed)
	myfeed = feedparser.parse('world.xml')
	myfeed_all.append(myfeed)
	# myfeed = feedparser.parse('http://sports.espn.go.com/espn/rss/news')

	# atom feed, strangely formatted, but it is working (5.3 Verified)
	myfeed = feedparser.parse('NYT_Home_Page.xml')
	myfeed_all.append(myfeed)

	# RSS feed, contain empty entry, but it is required to retain them (it shows in real feed!)
	myfeed = feedparser.parse('News_Toms_Hardware_US.xml')
	myfeed_all.append(myfeed)

	# RSS feed, working, but contain some styles that may crash email server
	myfeed = feedparser.parse('cnn_topstories_bug.rss')
	myfeed_all.append(myfeed)

	# Most entries "work" in these, some "not work" is basically something we cannot do.
	# The ads contain legal ending syntax that we cannot differentiate them
	# consider email SPAM filtering. We are very conservative, ensuring correctness.
	# Possibility of performance issue, used 1 sec to process
	# if you want "more working" version, we can implement more aggressive filter
	# techniques, such as, disgard all information afterwards which is seperated by 
	# 2 \n in a row, regardless of whats the characters in front
	#   NOTE THIS AGGRESSIVE METHOD WILL BREAK RSS WITH FORMATS, since they
	#   appear to have many \n after parsed and removed HTMLs
	myfeed = feedparser.parse('cardriver_blog.xml')
	myfeed_all.append(myfeed)
	
	myfeed = feedparser.parse('invalidurl')
	myfeed_all.append(myfeed)

	myfeed = feedparser.parse('www.yahoo.com')
	myfeed_all.append(myfeed)

	myfeed = feedparser.parse('')
	myfeed_all.append(myfeed)

	# NOT WORKING:

	# Possibility of performance issue, used 2 sec to process (over 300 entries)
	# some hyperlink ads remaining. We cannot do anything as those are "near content"
	# ads. We human are smart enough to comprehend the semantics!
	# some HTML code remains: &nbsp;
	# plan to remove it in the future
	# myfeed = feedparser.parse('http://www.rss-specifications.com/blog-feed.xml')

	# LC testing only

	# myfeed = feedparser.parse('http://feedparser.org/docs/examples/rss20.xml')
	# myfeed = feedparser.parse('http://feeds.feedburner.com/SlickdealsnetFP')
	# myfeed = feedparser.parse('http://rssfeeds.s3.amazonaws.com/goldbox')
	# myfeed = feedparser.parse('http://www.census.gov/mp/www/cpu/index.xml')

	# create a local temp file that store all parsed content for demostration purpose
	# firstly, check for feeds encoding and synchronize this information
	filename_counter = 1
	#latest_ts = 1
	# May 21, 00:00 GMT
	latest_ts = 1274400000
	total_stories = 0
	for onefeed in myfeed_all:
		filename = 'feed_test' + str(filename_counter) + '.txt'
		f = codecs.open(filename, encoding=onefeed.encoding, mode='w')

		# create a local log for indicating error
		errlog = open("ERRORLOG_testall.txt", mode ='a')

		# display global feed information that shared across all entries
		print 'Feed Encoding: ', onefeed.encoding
		print 'Feed version (type): ', onefeed.version

		# run specified parser corresponding to type of feeds (RSS,atom,others)
		debug = True
		#print '-----', type(onefeed.version)
		#if (onefeed.version is None):
		#	print 'LC CHECK DEBUG'
		if ((onefeed.version is not None) and (onefeed.has_key('version')) and (onefeed.version[:3] == "rss")):
			print 'VERBOSE: RSS feed detected!'
			stories = _RSS(f, errlog, onefeed, latest_ts, debug)
		elif ((onefeed.version is not None) and (onefeed.has_key('version')) and (onefeed.version[:4] == "atom")):
			print 'VERBOSE: ATOM feed detected!'
			stories = _ATOM(f, errlog, onefeed, latest_ts, debug)
		else:
			stories = []
			print 'UNKNOWN feed type! Probably invalid URL'
		filename_counter = filename_counter + 1
		total_stories = total_stories + len(stories[5])

	f.close()
	# print stories
	# return stories
	print 'Processed ', total_stories, ' stories!'
	return

def UpdateFeed():
	# DEBUG FLAG, LC DEBUG
	debug = False
	# create a local log for indicating error
	errlog = open("ERRORLOG.txt", mode ='a')
	print 'UPDATEFEED STARTED AT 997 \n'
	# connect to the database
	conn = MySQLdb.connect (host = "localhost", user = "root", passwd = "adminsql", db = "watercooler")

	# Get last updated time: latest_ts
	cursor = conn.cursor ()
	cursor.execute ("""
		SELECT feed_sources.source_name, feed_stories.time_stamp
		FROM feed_stories, feed_sources, (SELECT feed_sources.sid AS source_id
						FROM feed_stories, feed_sources
						WHERE feed_stories.sid = feed_sources.sid
						GROUP BY feed_sources.sid
						HAVING MAX(time_stamp)) AS source_filter
		WHERE feed_stories.sid = feed_sources.sid
		AND feed_sources.sid = source_filter.source_id
		ORDER BY feed_stories.time_stamp DESC;
		""")

	timestamp_list = cursor.fetchall ()
	# first item is the latest time!
	###(myfeed.entries[count].has_key('updated')	
	latest_ts = 0
	if (len(timestamp_list) > 0):
		latest_ts_tuple = timestamp_list[0]
		latest_ts = latest_ts_tuple[1]
	else:
		print ('TimeStamp POS Trapped 1, potential bug if not initial!')
		latest_ts = 1

	cursor.close ()


	# get a list of URL, source_URLs
	cursor1 = conn.cursor ()
	cursor1.execute ("""
		SELECT DISTINCT source_name, source_url
		FROM feed_sources
		ORDER BY source_name;
		""")
	sources_list = cursor1.fetchall ()
	if (len(sources_list) == 0):
		print ('INVALID SOURCE URL LIST, refer to log file!')
		errlog.write ('INVALID SOURCE URL LIST: LENGTH 0\n')
		cursor1.close ()
		conn.close ()
		return []

	source_URLs = []
	for source_item in sources_list:
		source_item_url = source_item[1]
		source_URLs.append(source_item_url)

	cursor1.close ()


	# for each URL in the URL list, parse things.....
	filename_counter = 0
	all_stories = []

	for source_URL in source_URLs:
		myfeed = feedparser.parse(source_URL)

		# get feed title and update feed title to database, if not null
		# we first get feed sid by URL, then update the title with sid
		source_feed_title = 'Undefined'
		if ((myfeed is not None) and (myfeed.feed is not None) and (myfeed.feed.has_key('title'))):
			source_feed_title = myfeed.feed.title
			source_feed_title = unicodedata.normalize('NFKD', source_feed_title).encode('ascii','ignore')
			source_feed_title = source_feed_title[:255]
			if (len(source_feed_title) > 0):
				cursor_title = conn.cursor ()
				cursor_title.execute ("""
                        SELECT sid
                        FROM feed_sources
						WHERE source_url = (%s);
                        """, (source_URL))
				feed_sid_tuple = cursor_title.fetchone ()
				if (len(feed_sid_tuple) > 0):
					feed_sid = feed_sid_tuple[0]
					cursor_update_title = conn.cursor ()
					cursor_update_title.execute ("""
							UPDATE feed_sources
							SET source_name = (%s)
							WHERE sid = (%s);
							""", (source_feed_title, feed_sid))

					cursor_update_title.close ()
				cursor_title.close ()
				if (len(feed_sid_tuple) > 0):
					conn.commit ()
			else:
				print 'NULL feed title detected, cannot update database for URL: ' , source_URL, '\n'
				errlog.write('NULL feed title detected, cannot update database for URL: ', source_URL, '\n')


		# create a local temp file that store all parsed content for demostration purpose
		# firstly, check for feeds encoding and synchronize this information
		# f = open("feeds.txt", "w")

		if (debug):
			filename = 'feed' + str(filename_counter) + '.txt'
			f = codecs.open(filename, encoding=myfeed.encoding, mode='a')
		else:
			f = codecs.open('placeholder_feed.txt', encoding=myfeed.encoding, mode='a')

		if (debug):
			# display global feed information that shared across all entries
			print 'Feed Encoding: ', myfeed.encoding
			print 'Feed version (type): ', myfeed.version

		# run specified parser corresponding to type of feeds (RSS,atom,others)
		if ((myfeed.version is not None) and (myfeed.has_key('version')) and (myfeed.version[:3] == "rss")):
			if (debug):
				print 'VERBOSE: RSS feed detected!'
			stories = []
			stories = _RSS(f, errlog, myfeed, latest_ts, source_URL, debug)
			
		elif ((myfeed.version is not None) and (myfeed.has_key('version')) and (myfeed.version[:4] == "atom")):
			if (debug):
				print 'VERBOSE: ATOM feed detected!'
			stories = []
			stories = _ATOM(f, errlog, myfeed, latest_ts, source_URL, debug)

		else:
			if (debug):
				print 'UNKNOWN feed type! Probably invalid URL'
			stories = []

		f.close()
		#print 'LC DEBUG 1125, STORY:'
		#print str(stories)
		#print '\n'
		all_stories.append(stories)
		filename_counter = filename_counter + 1

	# now i have a big list of stories: all_stories (list of many story)
	# story is [Feed Title, Entry Title, Entry Content, Entry Category, Entry URL, Entry Timestamp]

	# Process the List List:
	# 	Comparing the time stamp of each story with "newest" time stamp obtained
	# 	remove all old story
	processed_stories = []
	"""
	for r_story in all_stories:
		r_story_ts = r_story[5]
		if (r_story_ts > latest_ts):
			processed_stories.append(r_story)
	"""
	# now I have a processed list of stories as processed_stories
	# get list of IDs... sources_id_list
	cursor2 = conn.cursor ()
	cursor2.execute ("""
                SELECT DISTINCT sid, source_url
                FROM feed_sources
                ORDER BY sid;
                """)
	sources_id_list = cursor2.fetchall ()

	# story is [Feed Title, Feed URL, [Entry Titles], [Entry Contents], [Entry Categories], [Entry URLs], [Entry Timestamps]]
	cursor3 = conn.cursor ()
	cursor_chkexist = conn.cursor ()

	"""
	# LC DEBUG: DISPLAY ALL PROCESSED_STORIES
	debug_counter0 = 0
	for p_story in processed_stories:
		debug_counter = 0
		for item in p_story:
			print 'STORY ',  debug_counter0, 'Field ' , debug_counter, ': ', item
			debug_counter = debug_counter + 1
		debug_counter0 = debug_counter0 + 1
	"""
	# print 'LC DEBUG 1170, ALL STORIES '
	# print str(all_stories)
	# print '\n'
	# print 'LC DEBUG 1171, LENGTH ', str(len(all_stories))
	# print '\n'
	for p_story in all_stories:
		# holdings var for each p_story...
		list_title = []
		list_content = []
		list_category = []
		list_URL = []
		list_ts = []
		# print 'LC CHECK 1 ARRIVAL, PER STORY START' # LC DEBUG
		# loop to check against feed URL
		# print 'LC DEBUG 1176, P_STORY '
		# print str(p_story)
		# print '\n'
		# sys.stdout.flush()
		mysid = 0
		if (len(p_story) > 0):
			for id_list in sources_id_list:
				if (id_list[1][:255] == p_story[1][:255]):
					mysid = id_list[0] 
					break
			if (mysid == 0):
				print ('INVALID SID!, refer to log file! \n')
				errlog.write ('INVALID SID: Processed STORY\n')
				errlog.write('   FEED ENTRY TITLE IS:')
				errlog.write(p_story[1])
				errlog.write('\n')
				cursor_chkexist.close ()
				cursor2.close()
				cursor3.close()
				conn.commit()
				conn.close()
				return []
			# for each entry in the story...
			for iteration in (range(len(p_story[6]))):
				# check existence of entry
				cursor_chkexist.execute("""
					SELECT fid
					FROM feed_stories
					WHERE feed_stories.url = (%s);
					""", p_story[5][iteration][:255])
				entry_existence = cursor_chkexist.fetchall ()

				# story exist implies the len check > 0
				if (len(entry_existence) > 0):
					# check again if the content match
					cursor_getstory = conn.cursor ()
					cursor_getstory.execute ("""
								SELECT content
								FROM feed_stories
								WHERE feed_stories.url = (%s);
								""", p_story[5][iteration][:255])
					db_story = cursor_getstory.fetchall ()
					cursor_getstory.close ()
					if (len(db_story) == 0):
						print 'DEBUG 1093, db_story IS NULL:', db_story
					else:
						print 'HERE IS db_story RETURNED: ', db_story

					# safety check
					if (len(db_story) == 0):
						print 'DEBUG 1099, db_story is NULL when it should not be'
					else:
						if (len(db_story[0]) == 0):
							print 'DEBUG 1102, db_story[0] is NULL when it should not be'
						else:
							if (db_story[0][0][:255] != p_story[3][iteration][:255]):
								# add the entry to the list, as content does not match
								list_title.append(p_story[2][iteration][:255])
								list_content.append(p_story[3][iteration][:255])
								list_category.append(p_story[4][iteration][:255])
								list_URL.append(p_story[5][iteration][:255])
								list_ts.append(p_story[6][iteration])

							else:
								print 'DUPLICATE ENTRY DETECTED, CONTENT MATCHES'
							# delete that entry
							cursor_deletestory = conn.cursor ()
							cursor_deletestory.execute ("""
								DELETE FROM feed_stories
								WHERE feed_stories.url = (%s);
								""", p_story[5][iteration][:255])
							cursor_deletestory.close ()
							print 'HERE I REPLACE TO DB: ----------------'
				else:
					# implies entry does not exist, add to list
					list_title.append(p_story[2][iteration][:255])
					list_content.append(p_story[3][iteration][:255])
					list_category.append(p_story[4][iteration][:255])
					list_URL.append(p_story[5][iteration][:255])
					list_ts.append(p_story[6][iteration])

					print 'HERE I ADD TO DB: ----------------'

				# Add entry to DB
				print p_story[2][iteration][:255], ' ||| ' , p_story[3][iteration][:255], ' ||| ' , p_story[5][iteration][:255], ' ||| ' , str(p_story[6][iteration])
				print '-------------------------------------'
				cursor3.execute ("""
					INSERT INTO feed_stories (title, content, url, time_stamp, sid, gid)
					VALUES (%s, %s, %s, %s, %s, %s)
					ON DUPLICATE KEY UPDATE fid=fid+1;
					""", (p_story[2][iteration][:255], p_story[3][iteration][:255], p_story[5][iteration][:255], int(p_story[6][iteration]), mysid, 1))
				sys.stdout.flush()

			# make a final story out of the lists
			processed_story = []
			# CONSISTENCY CHECK
			con1 = len(list_title)
			con2 = len(list_content)
			con3 = len(list_category)
			con4 = len(list_URL)
			con5 = len(list_ts)
			if ((con1 != con2) or (con1 != con3) or (con1 != con4) or (con1 != con5)):
				print 'CONSISTENCY CHECK FAILED IN ATOM STORY '
				print 'STORY IS:'
				print str(story)
				print '\n'
			if (len(list_ts) > 0):
				processed_story = [p_story[0][:255], p_story[1][:255], list_title, list_content, list_category, list_URL, list_ts]
				processed_stories.append(processed_story)

	cursor_chkexist.close ()
	cursor3.close ()
	cursor2.close ()
	conn.commit ()
	conn.close ()
		
	# return processed stories list
	# LC DEBUG
	# print processed_stories
	return processed_stories

if __name__ == "__main__":
	UpdateFeed()


# Copyright information
"""
Authors: CS 130 Watercooler Content Distribution Engine Team
Copyright (c) 2010, CS 130 Watercooler Content Distribution Engine Team
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
"""
feedparser module (Universal Feed Parser)
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
