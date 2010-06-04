#!/usr/bin/python2.6

import smtplib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

from urlparse import urlparse

import sys
import Database

HOST = {'AT&T':'txt.att.net',
        'T-Mobile':'tmomail.net',
        'Verizon':'vtext.com',
        'SprinT':'messaging.sprintpcs.com'}

SENDER = 'Sender@watercooler.geogriffin.info'

def sendAsEmail(emailAddr, message):
    """(Internal Use) Send email using smtplib
    
    Input is a destination email address and a MIMEMultipart message. This
    function is intended to be used with formatEmail function. However, any
    other function that supples the right input can work.
    """
    # Create a SMTP connection. It is assumed that a
    # MTA is set up locally.
    smtpObj = smtplib.SMTP('localhost')

    sendlist = []
    sendlist.append(emailAddr)

    # TODO: Catch the exceptions.
    smtpObj.sendmail(SENDER, sendlist, message.as_string())

def sendAsText(phoneNum, provider, subject, body):
    """(Internal Use) Send text message (SMS) through email
    
    This function composes the destination address using the following
    format: phone_number@provider_server. If you have AT&T, the destination
    address may be sent like the following: 5553872638@txt.att.net. This
    works because major phone provider have servers that delivers email
    messages to user's phone using SMS. 
    """
    # Create a SMTP connection. It is assumed that a
    # MTA is set up locally.
    smtpObj = smtplib.SMTP('localhost')

    # Check that provider provided is one of the hosts we support
    if provider not in HOST:
        print "We do not support " + provider + "."
        return

    # Remove '-' from phone number
    phoneNum = phoneNum.replace("-", "")

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

def formatEmail(feed, emailAddr):
    """(Internal Use) Compose the an email based a given feed and destination
    
    Returns a MIMEMultiplat message. 
    """
    
    # Rename variables
    feedURL = feed[0] 
    entries_URL = feed[1]
    entries_titles = feed[2]
    entries_contents = feed[3]

    # Find the Subject of the Email
    parsed_url = urlparse(feedURL)
    subject = parsed_url.netloc

    # Construct the message body
    message = MIMEMultipart()
    message['Subject'] = subject
    message['From'] = SENDER
    message['To'] = emailAddr
    
    # Form an html form of the email body  
    body = "<html><head></head><body>"
 
    # I assume that there are same number of entries_URL, entries_titles,
    # and entries_contents are the same. 
    numEntries = len(entries_URL)
    for index in range(numEntries):
        
        link = "<a href=\"" + entries_URL[index] + "\">" + \
                entries_titles[index] + "</a>" 
        content = entries_contents[index]
        body += link + "<br />" + content

        if index < (numEntries - 1):
               body += "<br /><br />"
    
    body += "</body></html>"
    message.attach(MIMEText(body, "html"))
    
    return message

def sendFeedAsSMS(feed, user):
    """(Internal Use) Send the stories in the given feed to the given user

    """
    
    # Rename variables
    entries_URL = feed[1]
    entries_titles = feed[2]
    entries_contents = feed[3]

    phoneNum = user[2]
    provider = user[3]
    send_method = user[4]
 
    numEntries = len(entries_titles)
    
    # Send each story to given user. I assume that there are same number of 
    # entries_URL, entries_titles, and entries_contents are the same.
    for index in range(numEntries):
        if send_method == "sms_text":
            sendAsText(phoneNum, provider, entries_titles[index], \
                        entries_contents[index])
        elif send_method == "sms_link":
            sendAsText(phoneNum, provider, entries_titles[index], \
                        entries_URL[index])

def sendStories(listOfFeeds):
    """(API) Send the stories in the list of feeds to the subscribers

    For each of the listOfFeeds, sendStories will pull a list of users 
    who subscribe to that feed. Then, it will send the stories to users
    based on the receiver's perfer receiving method.
    """
    for feed in listOfFeeds:
       
        # Rename variables 
        feedURL = feed[0]
        entries_URL = feed[1]
        entries_titles = feed[2]
        entries_contents = feed[3]
        
        listOfUsers = Database.getUsersBySourceURL(feedURL)
        
        for user in listOfUsers:
            
            # Rename variables
            username = user[0]
            emailAddr = user[1]
            phone = user[2]
            carrier = user[3]
            send_method = user[4] 
            
            # Send stories based on user's prefer method
            if send_method == "email":
                message = formatEmail(feed, emailAddr)
                sendAsEmail(emailAddr, message)
            
            elif send_method == "sms_text" or send_method == "sms_link":
                sendFeedAsSMS(feed, user)

def sendConfirmEmail(link, username, emailAddr):
    """(API) Send confirmation Email to user
    
	All inputs are strings. Link is the link you want user to click. 
    """
    # Construct the message body
    message = MIMEMultipart()
    message['Subject'] = "Please confirm your Email address"
    message['From'] = SENDER
    message['To'] = emailAddr
    
    # Form an html form of the email body  
    body = "<html><head></head><body>"
     
    content = "Hello " + username + ",<br /><br />Thank you for using Watercooler. Please click on the link below to confirm your Email address:<br \><br \><a href=\"" + link + "\">Confirm</a>"
    
    body += content
    body += "</body></html>"
    message.attach(MIMEText(body, "html"))
    
    sendAsEmail(emailAddr, message)

def sendConfirmSMS(phoneNum, provider, username, pin):
    """(API) Send confirmation SMS to user
	
	All inputs are strings, including pin.
    """
	# Form body of message
    subject = "PIN:" + pin
    content = "Thank you for using Watercooler. Please enter this pin in your settings page."
	
	# Send message as SMS
    sendAsText(phoneNum, provider, subject, content)


