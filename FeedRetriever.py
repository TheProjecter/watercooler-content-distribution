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

"""

# a function to remove trash, specifically HTML codes for content
def _ContentCutter(content):
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

# a helper function to display global feed information
def _DisplayGlobal(myfeed, type):

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
#		   myfeed = feedparser parsed object
def _RSS(f, myfeed):
	# calculate how many "entries" in the feed
	n_entries = len(myfeed['entries'])
	print 'There are' , n_entries , 'entries in the feed'

	# print details of the feeds (global):
	_DisplayGlobal(myfeed,'RSS')

	# display first entry info on screen (first part)
	# print ''
	# print 'First Entry Information:'
	# print 'Title:' , myfeed.entries[0].title

	# write all entries parsed on local file
	for count in range(n_entries):
		f.write('Entry ' + str(count+1) + ' Information:\n')
		f.write('Title: '+ myfeed.entries[count].title+'\n')

		# process content info (clear out HTML codes)
		content = myfeed.entries[count].description
		content = _ContentCutter(content)

		# display first entry info on screen (second part)
		if (count == 0):
			print 'Link: ', myfeed.entries[0].link
			print 'ID: ', myfeed.entries[0].id
			print 'Content:' , content

		f.write('Content: ' +content+'\n')

		# write date of entries
		last_updated = myfeed.entries[count].date_parsed
		f.write('Last Updated: '+str(last_updated[0])+'/'+ str(last_updated[1]) +'/'+ str(last_updated[2])+'\n\n')
	return


def _ATOM(f, myfeed):
	# calculate how many "entries" in the feed
	n_entries = len(myfeed['entries'])
	print 'There are' , n_entries , 'entries in the feed'

	# print details of the feeds (global):
	_DisplayGlobal(myfeed,'ATOM')
	
	# display first entry info on screen (first part)
	print ''
	print 'First Entry Information:'
	print 'Title:' , myfeed.entries[0].title

	# write all entries parsed on local file
	for count in range(n_entries):
		f.write('Entry ' + str(count+1) + ' Information:\n')
		f.write('Title: '+ myfeed.entries[count].title+'\n')

		# process content info (clear out HTML codes)
		pos = 0
		# content = myfeed.entries[count].summary
		# intialize content variable to empty string (very useful)
		content = ''

		# get content from description field first, if possible
		content = myfeed.entries[count].description

		# if the content is empty, try to get in content field (atom specific)
		if content == '':
			#print 'VERBOSE: GET content from ".CONTENT"'
			# when entries[count] has content, get the content out
			if myfeed.entries[count].has_key('content'):
				for content_index in range(len(myfeed.entries[count].content)):
					content = myfeed.entries[count].content[content_index].value
					if len(content) != 0:
						#print 'LCDEBUG CONTENT LENGTH: ', len(content)
						#print 'LCDEBUG CONTENT: ',content
						break
		#else:
			#print 'VERBOSE: GET content from ".DESCRIPTION"'

		# when RSS content field also empty string, we cannot do anything more
		if content == '':
			print 'VERBOSE: content is empty, nothing displayed'
			print ''
		else:
			#print 'VERBOSE: Processing content'
			content = _ContentCutter(content)

			# display first entry info on screen (second part)
			if (count == 0):
				print 'Link: ', myfeed.entries[0].link
				print 'ID: ', myfeed.entries[0].id
				#if (pos != 0):
				#	print 'Content:' , content[:pos]
				#else:
				print 'Content:' , content

			# write content of entries
			#if (pos != 0):
				#print('POS, INDEX DEBUG: ', pos, count)
			#	f.write('Content: ' +content[:pos]+'\n')
			#else:
			f.write('Content: ' +content+'\n')

			# write date of entries
			last_updated = myfeed.entries[count].date_parsed
			f.write('Last Updated: '+str(last_updated[0])+'/'+ str(last_updated[1]) +'/'+ str(last_updated[2])+'\n\n')
	return


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
	# myfeed = feedparser.parse('http://feeds.nytimes.com/nyt/rss/HomePage')


	# NOT WORKING:
	# some ADs are considered blank entries, there are trash words that are not HTML
	# seems impossible to get rid of
	# The ADs problem can be solved by removing blank content entries
	# But we cannot do it now as it may hide bugs
	myfeed = feedparser.parse('http://feeds.pheedo.com/toms_hardware_headlines')

	# Leo tried to remove all HTML trash already, but the formatting of output still wierd
	# myfeed = feedparser.parse('http://feeds.feedburner.com/caranddriver/blog')


	# create a local temp file that store all parsed content for demostration purpose
	# firstly, check for feeds encoding and synchronize this information
	# f = open("feeds.txt", "w")
	myfeed_encoding = myfeed.encoding
	f = codecs.open('feeds.txt', encoding=myfeed_encoding, mode='w')

	# display global feed information that shared across all entries
	print 'Feed Encoding: ', myfeed_encoding
	print 'Feed version (type): ', myfeed.version

	# run specified parser corresponding to type of feeds (RSS,atom,others)
	if (myfeed.version[:3] == "rss"):
		print 'VERBOSE: RSS feed detected!'
		_RSS(f, myfeed)
	elif (myfeed.version[:4] == "atom"):
		print 'VERBOSE: ATOM feed detected!'
		_ATOM(f, myfeed)
	else:
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
