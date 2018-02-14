# Dead Simple Wordpress Amazon S3 Backups

## Plugin Info

Author: Sole Graphics

Contributors: Ben Greene,

Purpose: allows for scheduling site backups of your WordPress site's database and uploads directory to an AWS bucket.

Licensing: MIT


## Setup

To get the plugin up and running, you'll have to add a bit of information in the settings page:

* AWS Credentials
	- Key
	- Secret
* Backup Destination
	- Bucket Name
* AWS Settings
	- Region (if you don't know your region, you can check out [Amazon's list of regions](http://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region))


There are some optional settings as well:


* Notification Address
	This is for getting email confirmation on successful backups. You can add multiple addresses, just seperate them with a ','.
* Backup Schedule
	- How often to automatically backup the database
	- How often to automatically backup the uploads directory

You can also manually backup the database and uploads by clicking the "Backup" button on the settings page.


## Multisites

If your site is a multisite, then you will only be able to update the plugin's settings from the network admin view. The zipped uploads folder and the database dump will contain all instance's uploads and database tables, respectively. 


## How To Contribute

There are a couple ways you can contribute:

First, you can report any issues you come across. Issues can be reported via [Github Issues](https://github.com/SoleGraphics/Dead-Simple-Wordpress-Amazon-S3-Backups/issues). Please give detailed descriptions of the issue, and (if possible) steps to duplicate the problem.

Second (for developers) feel free to fork the repo, make whatever changes you think are beneficial/appropriate, and then make a PR. If you choose this option, PLEASE add a detailed description of your change AND WHY you added it.
