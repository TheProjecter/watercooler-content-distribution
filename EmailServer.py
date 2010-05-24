#!/usr/bin/python2.6

import smtplib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

import sys
import Database

HOST = {'AT&T':'txt.att.net',
        'T-Mobile':'tmomail.net',
        'Verizon':'vtext.com',
        'SprinT':'messaging.sprintpcs.com'}

SENDER = 'Sender@watercooler.geogriffin.info'

def sendAsEmail(emailAddr, subject, body):
    """Use program mail to send mail to specified emailadddr
    
    This function send the mail using mail program
    and whatever MTA mail is using to send mail. All inputs
    must be strings.
    """

    # Construct the message body
    message = MIMEMultipart()
    message['Subject'] = subject
    message['From'] = SENDER
    message['To'] = emailAddr
    message.attach(MIMEText(body, 'plain'))

    # Create a SMTP connection. It is assumed that a
    # MTA is set up locally.
    smtpObj = smtplib.SMTP('localhost')

    sendlist = []
    sendlist.append(emailAddr)

    # TODO: Catch the exceptions.
    smtpObj.sendmail(SENDER, sendlist, message.as_string())

def sendAsText(phoneNum, provider, subject, body):
    """Use program mail to send text.
    
    This function send text using mail program. All inputs
    are strings. Input provider must be found in HOST as defined
    on the top of this script.    
    """
    # Create a SMTP connection. It is assumed that a
    # MTA is set up locally.
    smtpObj = smtplib.SMTP('localhost')

    # Check that provider provided is one of the hosts we support
    if provider not in HOST:
        print "We do not support " + provider + "."
        return

    # Set Email Address
    emailAddr = phoneNum + "@" + HOST[provider] 

    # Construct the message body
    message = MIMEMultipart()
    message['Subject'] = subject
    message['From'] = SENDER
    message['To'] = emailAddr
    message.attach(MIMEText(body, 'plain'))

    sendlist = []
    sendlist.append(emailAddr)

    # TODO: Catch the exceptions.
    smtpObj.sendmail(SENDER, sendlist, message.as_string())    

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
    
        listOfUsers = Database.getUsersByStory(storyURL)
        
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
                print "Send mail to:", userEmail
                print "title:", storyTitle
                print "\n"
                #print "storyContent:", storyContent
                sendAsEmail(userEmail, storyTitle, storyContent)
            elif userMethod == "sms_text":
                print "Send text (without link) to phone#:", userPhone
                #print "carrier:", userCarrier
                print "title:", storyTitle
                print "\n"
                #print "storyContent:", storyContent
                sendAsText(userPhone, userCarrier, storyTitle, storyContent)
            elif userMethod == "sms_link":
                print "Send text (with link) to phone:", userPhone
                #print "carrier:", userCarrier
                print "title:", storyTitle
                print "\n"
                #print "storyURL:", storyURL
                sendAsText(userPhone, userCarrier, storyTitle, storyURL)
