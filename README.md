# Taghound Media Tagger #
**Contributors:** jplhomer  
**Tags:** media gallery, tagging, images, deep learning, neural network, admin  
**Requires at least:** 3.9  
**Tested up to:** 4.5  
**Stable tag:** 1.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Automatically tag and search images in your media library using Clarifai's object recognition API.

## Description ##

Automatically tag and search images in your media library using [Clarifai's object recognition API](https://clarifai.com/). Using advances in deep learning, Clarifai leverages convoluted neural networks to analyze an image and return predictions of the contents of that image.

Typically, your images will be tagged with simple indicators like:

- one person
- no people
- happy
- man/woman
- outdoors
- tree

Taghound Media Tagger takes these predictions and turns them into WordPress tags. This enables you to immediately search for images you've added to your media library by the contents of the image and not by the title or description you have manually entered.

Currently Taghound Media Tagger will only tag images you add **after** installing the plugin. *Note that we plan to offer support for backdating your media library. See the Coming Soon section below.*

### Features ###

- Automatically tag images in your media gallery with predicted contents
- Search for the contents of images using the Media Gallery search input
- Filter your media gallery by a specific tag

### Coming Soon ###

We're always looking to improve Taghound Media Tagger. Here's what is on the roadmap:

- Tag videos in addition to photos
- Backdate your media library

### Github ###
This plugin is open-source and available on Github. Please consider contributing to the plugin if you find bugs or have a feature you'd like to see implemented:

https://github.com/jplhomer/taghound-media-tagger

[![Build Status](https://travis-ci.org/jplhomer/taghound-media-tagger.svg)](https://travis-ci.org/jplhomer/taghound-media-tagger)

### Website ###
http://jplhomer.org/projects/taghound-media-tagger/

## Installation ##

1. Upload 'taghound-media-tagger' to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Visit the [Clarifai Developer Portal](https://developer.clarifai.com/) and create a free or paid account
1. Create an application with Clarifai, and copy the **Client ID** and **Client Secret** from the developer portal
1. Visit your WordPress Media Settings page under Settings > Media
1. Paste in the **Client ID** and **Client Secret** from step 4 and click "Save Changes"
1. On the same page, click the checkbox "Enable for all new Images" to have Taghound Media Tagger begin tagging new images!

## Frequently Asked Questions ##

### Q. How do my images get analyzed by Clarifai? ###
A. Taghound Media Tagger sends each of your images over HTTPS to Clarifai after they've been uploaded to your media gallery.

### Q. What does Clarifai do with my images? ###
A. Clarifai processes your images and returns a list of associated keywords. Additionally, Clarifai stores images to further train its models and increase the accuracy of future responses. [Clarifai does not share your images with third parties](https://www.reddit.com/r/clarifai/comments/4aqhmr/question_about_the_api_does_it_take_long_because/d13fnz5).

### Q. Does this plugin process images that already exist in my media library? ###
A. Not yet, but soon! We hope to push backdating capabilities as an update.

### Q. What if an image gets tagged with something I don't like? ###
A. Simply untag the image by clicking the 'X'. This is similar to how you would remove a tag from a post.

## Screenshots ##

### 1. Tags are automatically applied to images when added to the media library ###
![Tags are automatically applied to images when added to the media library](http://s.wordpress.org/extend/plugins/taghound-media-tagger/screenshot-1.png)

### 2. Tags are searchable from within the media library ###
![Tags are searchable from within the media library](http://s.wordpress.org/extend/plugins/taghound-media-tagger/screenshot-2.png)

### 3. Tags are searchable from within the Insert Media modal window when editing a post, too ###
![Tags are searchable from within the Insert Media modal window when editing a post, too](http://s.wordpress.org/extend/plugins/taghound-media-tagger/screenshot-3.png)

### 4. Filter media items by a specific tag when using the list view of the media library ###
![Filter media items by a specific tag when using the list view of the media library](http://s.wordpress.org/extend/plugins/taghound-media-tagger/screenshot-4.png)


## Changelog ##

### 1.0.0 ###
* Taghound Media Tagger.
