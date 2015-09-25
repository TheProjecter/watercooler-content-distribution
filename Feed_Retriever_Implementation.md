> # Feed Retriever TASKS: #

1 day has 2 units of “time”
List of task (must)
  1. : Able to handle RSS feeds (2 units)
  1. : Able to handle ATOM feeds (4 units)
  1. : Parse out individual fields (4 units)
  1. : communicate with the database (2 units)
  1. : Process the fields (20 units)
  1. : Wrap up information into stories (2 units)

list of task (should)
  1. : Categorize the entries of feeds if supported (8 units)
  1. : parallelize the loop to retrieve different feeds so that they can be done together (8 units)

list of task (may)
  1. : allow plug-in of content processor so that the retriever can process a specific feed URL that is so special that cannot be done using universal algorithm (10 units)


# Sequence of steps that Feed Retriever would do #
Steps 1-7 will be wrapped as a function call
Step 8 is a separate function call, requiring previous one to be called first
  1. : Obtain latest time stamp from database (so as to know which feeds we retrieve later is new)
  1. : Obtain a list of feeds URL that need to be retrieved from database
  1. : Put the URLs into a Python list
  1. : for each URL in the list:
    1. .	Using Universal Feed Parser (UFP) to download and parse the feed
    1. .	calculate how many entries there are in the feed
    1. .	for each entry of the feed
      1. .	Get the fields out of the UFP
      1. .	Process the “content” by removing HTML codes
      1. .	Further process the “content” by removing extra whitespaces or punctuations
      1. .	calculate the time stamp (similar to UNIX time format)
      1. .	Put the required fields together into a list. This is a story as defined.
      1. .	Put this list into a global List List
  1. : Now we have a List List (stories) (but excess)
  1. : Process the List List:
    1. .	Comparing the time stamp of each story with “newest” time stamp obtained
    1. .	remove all old story
  1. : Update the database with the processed List List, by unwrapping the List List and update the database field by field
  1. : Give out the processed List List (stories)


# Database team please help the following: #
Definition: LAST\_UPDATED SHOULD BE IN UNIX MODE, TO UNIFY ALL INTERPRETION
STORY: [name, title name, content, category, entry URL, timestamp](feed.md)
This is containing extra information for email/SMS, but is essential for user scanner to do useful things without talking to database frequently. I believe given feed name, user scanner can query user name and hence user information easily.

Here is what we need: Please provide codes to do below things
  * .	Given: nothing
> > Output: Please talk to the database and give out latest timestamp
> > Hint: make a DEFAULT (TITLE), URL localhost, feed which store this special timestamp and hence retrieve it, or use traditional query to do it if not.
  * .	Given: nothing
> > Output: Please give out all SOURCE\_URL as a list, found in FEEDS SOURCES
  * .	Given: latest timestamp
> > Output: Please update the database with this timestamp if using DEFAULT tactics. Otherwise, this is not needed
  * .	Given: a story (a list of elements) such as:
> > [name, title name, content, category, entry URL, timestamp](feed.md)


> Please update/create the database accordingly:
  * title name  -> TITLE in FEED ENTRIES
  * content -> CONTENT in FEED ENTRIES
  * entry URL -> URL in FEED ENTRIES
  * timestamp -> LAST\_UPDATED in FEED ENTRIES
  * depending on DB implementation, you may need to update LAST\_UPDATED of FEEDS SOURCE accordingly