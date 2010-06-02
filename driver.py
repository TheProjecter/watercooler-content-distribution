#!/usr/bin/python2.6
"""
Version LOG

1.0:
Just a basic version including feed retriever

2.0:
A Looping functional driver 

2.1:
Adding driver log to facilitate debug

2.2:
Added more debug output

2.3:
Lengthened the looping frequency to 3 mins

2.3.1:
Reduce initial iteration delay to 30 seconds
Enable logs by default for the testing purpose

2.4:
Change dereferencing index for the stories list
This is to synchronize with the story definition change in FeedRetriever 6.6.3

"""
global debug
debug = False
logs = True
import FeedRetriever
import EmailServer
import time

def Driver():
	# a driver to debug
	if (logs):
		driverlog = open("DRIVERLOG.txt", mode ='w')
		storylog = open("STORYLOG.txt", mode ='w')

	# a loop to call all backend functions
	while (True):
		# gather new feeds entries
		stories = FeedRetriever.UpdateFeed()

		if (debug):
			# current debug/testing purpose
			print 'story is [Feed Title, Feed URL, Entry Title, Entry Content, Entry Category, Entry URL, Entry Timestamp]'
			for index, story in enumerate(stories):
				print 'Story', index, ':'
				for item in range(len(story)):
					if (item <= 4):
						if ((item == 2) and (len(story[item]) > 100)):
							print 'Item', item , ':', story[item][:100], '...'
						else:
							print 'Item', item , ':', story[item]
					else:
						print 'Item', item , ':', story[item]
				print ''

		if (logs):
			storylog.write(str(stories))
			storylog.write('\n\n')
		# for testing, only get first four stories, and trim the 
		cutted_stories = []
		limiter = 0
		for story in stories:
			if limiter < 1000:
				cutted_story = []
				cutted_story.append(str(story[5]))
				cutted_story.append(str(story[2]))
				cutted_story.append(str(story[3]))
				cutted_stories.append(cutted_story)
				limiter = limiter + 1
			if ((limiter % 10) == 0):
				# call tim's function
				# special code to delay Tim's code to avoid bombing, to facilitate testing
				print ' -------------------------------------- '
				print 'Here is 10 story passed to email server'
				print ' -------------------------------------- '
				if (logs):
					driverlog.write('--------------------------------------\n')
					driverlog.write('Here is 10 story passed to email server\n')
					driverlog.write('--------------------------------------\n')
					driverlog.write(str(cutted_stories))
					driverlog.write('\n')
					driverlog.flush()

				EmailServer.sendStories(cutted_stories)
				time.sleep(30)
				cutted_stories = []
		print ' ------------------------------------------ '
		print 'Here is remaining ', str(limiter % 10), ' story passed to email server'
		print ' ------------------------------------------ '
		if (logs):
				driverlog.write('--------------------------------------\n')
				driverlog.write('Here is remaining ' + str(limiter % 10) + ' story passed to email server\n')
				driverlog.write('--------------------------------------\n')
				driverlog.write(str(cutted_stories))
				driverlog.write('\n')
				driverlog.flush()
		EmailServer.sendStories(cutted_stories)
		time.sleep(180)
		cutted_stories = []
		# print cutted_stories
	if (logs):
		driverlog.close()
		storylog.close()

	return

if __name__ == "__main__":
	Driver()







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