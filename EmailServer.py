#! /usr/bin/env python

import subprocess, shlex
import sys
import database # TODO: database is currently a test script

HOST = {'AT&T':'txt.att.net',
        'T-Mobile':'tmomail.net',
        'Verizon':'vtext.com',
        'SprinT':'messaging.sprintpcs.com'}

def runBashPipe(frontPipe, backPipe):
    """Run the two inputs as commands joined by a pipe
    
    This function is used as an internal function to help
    implementing sendAsEmail and sendAsText. It takes two
    lists and treat them as bash commands. The first
    command is executed and its output is sent to second
    command as input.
    """
    # Split the bash commands into python lists
    frontPipe = shlex.split(frontPipe)
    backPipe = shlex.split(backPipe)

    # Execute the bash commands using subprocess
    p1 = subprocess.Popen(frontPipe, stdout=subprocess.PIPE)
    p2 = subprocess.Popen(backPipe, stdin=p1.stdout, stdout=subprocess.PIPE)
    output = p2.communicate()[0]    

def sendAsEmail(emailAddr, subject, body):
    """Use program mail to send mail to specified emailadddr
    
    This function send the mail using mail program
    and whatever MTA mail is using to send mail. All inputs
    must be strings.
    """
    # Set the bash commands to be executed
    frontPipe = "echo " + body
    backPipe = "mail -s \"" + subject + "\" " + emailAddr

    # Execute the command
    runBashPipe(frontPipe, backPipe)

def sendAsText(phoneNum, provider, subject, body):
    """Use program mail to send text.
    
    This function send text using mail program. All inputs
    are strings. Input provider must be found in HOST as defined
    on the top of this script.    
    """
    # Check that provider provided is one of the hosts we support
    if provider not in HOST:
        print "We do not support " + provider + "."
        sys.exit(1)

    # Set Email Address
    emailAddr = phoneNum + "@" + HOST[provider] 

    # Set the bash commands to be executed
    frontPipe = "echo " + body
    backPipe = "mail -s \"" + subject + "\" " + emailAddr

    runBashPipe(frontPipe, backPipe)

def sendStories(listOfStoriesURL):
    """Send the given list of stories to users who subscribe to them
    
    The input to sendStories is of the following form:
    [storyURL, storyTitle, storyContents]
    For example,
    ["www.yahoo.com", "yahoo", "This is yahoo"]

    this function does not return anything.
    """
    for story in listOfStoriesURL:

        # Get story info
        storyURL = story[0]
        storyTitle = story[1]
        storyContent = story[2]
        
        listOfUsers = database.getUsersByStory(storyURL)
        
        for user in listOfUsers:
        
            # Get user info
            userEmail = user[1]
            userPhone = user[2]
            userCarrier = user[3]
            userMethod = user[4]

            # Format user info
            userPhone = userPhone.replace("-", "")
            
            # Send
            if userMethod == "email":
                sendAsEmail(userEmail, storyTitle, storyContent)
                print "sendAsEmail"
                print "mail:", userEmail
                print "title:", storyTitle
                print "storyContent:", storyContent
            elif userMethod == "sms_text":
                sendAsText(userPhone, userCarrier, storyTitle, storyContent)
                print "sendAsText"
                print "phone:", userPhone
                print "carrier:", userCarrier
                print "title:", storyTitle
                print "storyContent:", storyContent
            elif userMethod == "sms_link":
                sendAsText(userPhone, userCarrier, storyTitle, storyURL)
                print "sendAsText"
                print "phone:", userPhone
                print "carrier:", userCarrier
                print "title:", storyTitle
                print "storyURL:", storyURL
