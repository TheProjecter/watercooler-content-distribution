"""
Version LOG

1.0:
Just a basic version including feed retriever

"""
global debug
debug = True
import FeedRetriever

def Driver():
	stories = FeedRetriever.UpdateFeed()

	if (debug):
		# current debug/testing purpose
		print 'story is [Feed Title, Entry Title, Entry Content, Entry Category, Entry URL, Entry Timestamp]'
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