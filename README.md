[![Build Status](https://travis-ci.org/jwensley2/HipChat-Bot.svg?branch=master)](https://travis-ci.org/jwensley2/HipChat-Bot)

# HipChat Bot

## Requirements
- PHP 5.5+
- A Database (Only tested with MySQL currently, but others should work)
- [Composer](https://getcomposer.org/)

## Installation
1. Clone or download and unzip the repository  
2. Rename [.env.example](.env.example) to .env and edit with your own configuration
3. run `composer install`
4. run `php artisan migrate`
5. Navigate to http://{yourdomain}/addon/capabilities, you should see a JSON string with the addons capabilities
6. Install to HipChat
    1. Navigate to https://hipchat.com/rooms
    2. Click the name of the room to install to (you must be the owner of the room or a group administrator)
    3. Click integrations
    4. At the bottom of the "Manage" tab click "Install an integration from a descriptor URL"
    5. Enter the Integration URL http://{yourdomain}/addon/capabilities and click "Add Integration"
7. Test it out, go to the room you installed to and type /joebot (or whatever you set the command to)

## Usage
The default command is /joebot, but can be changed through configuration

## Current and Planned Features
- [x] /joebot roll [max=100]  
    Rolls a random number between 0 and max
- [x] /joebot math \<expression\>, aliases /calc  
    Calculates the result of a math expression
- [x] /joebot aww  
    Get an random image from /r/aww
- [x] /joebot invite @user
    Invite a user to the current channel
