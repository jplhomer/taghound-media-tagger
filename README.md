# Taghound Media Tagger #
**Contributors:** [jplhomer](https://profiles.wordpress.org/jplhomer)  
**Tags:** media gallery, tagging, images, deep learning, neural network, admin  
**Requires at least:** 3.9  
**Tested up to:** 4.7  
**Stable tag:** 1.2.0  
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

Taghound Media Tagger lets you tag new images in addition to a backlog of older images you added before installing the plugin!

### Features ###

- Automatically tag images in your media gallery with predicted contents
- Search for the contents of images using the Media Gallery search input
- Filter your media gallery by a specific tag
- Tag existing images in your media library with the click of a button

### Coming Soon ###

We're always looking to improve Taghound Media Tagger. Here's what is on the roadmap:

- Tag videos in addition to photos
- Upgrade to Clarifai's V2 API

### Github ###
This plugin is open-source and available on Github. Please consider contributing to the plugin if you find bugs or have a feature you'd like to see implemented:

https://github.com/jplhomer/taghound-media-tagger

[![Build Status](https://travis-ci.org/jplhomer/taghound-media-tagger.svg)](https://travis-ci.org/jplhomer/taghound-media-tagger)

### Website ###
http://jplhomer.org/projects/taghound-media-tagger/

### Art ###
Special thanks to [Kevin Fish](http://www.kevinfishdesigns.com/) for his work on the TagHound logo and banner!

## Installation ##

1. NOTE: You must have PHP 5.5 or greater installed on your server to activate the plugin. Ask your web host about this if you are unsure.
1. Upload 'taghound-media-tagger' to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Visit the [Clarifai Developer Portal](https://developer.clarifai.com/) and create a free or paid account
1. Create an application with Clarifai, and copy the **Client ID** and **Client Secret** from the developer portal
1. Visit the Taghound settings page under Settings
1. Paste in the **Client ID** and **Client Secret** from step 4 and click "Save Changes"
1. On the same page, click the checkbox "Enable for all new Images" to have Taghound Media Tagger begin tagging new images!

## Frequently Asked Questions ##

### Q. How do my images get analyzed by Clarifai? ###
A. Taghound Media Tagger sends each of your images over HTTPS to Clarifai after they've been uploaded to your media gallery.

### Q. What does Clarifai do with my images? ###
A. Clarifai processes your images and returns a list of associated keywords. Additionally, Clarifai stores images to further train its models and increase the accuracy of future responses. [Clarifai does not share your images with third parties](https://www.reddit.com/r/clarifai/comments/4aqhmr/question_about_the_api_does_it_take_long_because/d13fnz5).

### Q. Does this plugin process images that already exist in my media library? ###
A. Yes! Visit the **Settings > Taghound** page and use the Bulk Tagger tool.

### Q. What if an image gets tagged with something I don't like? ###
A. Simply untag the image by clicking the 'X'. This is similar to how you would remove a tag from a post.

### Q. Can I use this plugin with other 3rd party plugins like Media Library Assistant? ###
A. Yes! We've provided a hook for you to choose where to store the tags. Simply pass a function that returns the slug of the taxonomy you'd like to use to `tmt_tag_taxonomy` inside your theme's **functions.php** file.

**[See the documentation here](https://gist.github.com/jplhomer/05a6033e544c16cf335f2b163ff33069).**

We've also tested this with a couple popular media management plugins:

* [Media Library Assistant](https://wordpress.org/plugins-wp/media-library-assistant/) uses the `attachment_tag` slug
* [Enhanced Media Library](https://wordpress.org/plugins-wp/enhanced-media-library/) uses the `media_category` slug

Note that the default user interface for Taghound's tag manipulation will be hidden when you've chosen an alternate tag taxonomy.

## Screenshots ##

### 1. Tags are automatically applied to images when added to the media library ###
![Tags are automatically applied to images when added to the media library](http://ps.w.org/taghound-media-tagger/assets/screenshot-1.png)

### 2. Tags are searchable from within the media library ###
![Tags are searchable from within the media library](http://ps.w.org/taghound-media-tagger/assets/screenshot-2.png)

### 3. Tags are searchable from within the Insert Media modal window when editing a post, too ###
![Tags are searchable from within the Insert Media modal window when editing a post, too](http://ps.w.org/taghound-media-tagger/assets/screenshot-3.png)

### 4. Filter media items by a specific tag when using the list view of the media library ###
![Filter media items by a specific tag when using the list view of the media library](http://ps.w.org/taghound-media-tagger/assets/screenshot-4.png)


## Changelog ##

### 1.2.0 ###
* FEATURE: Adds the `tmt_tag_taxonomy` filter to allow users to customize the taxonomy used to store tags

### 1.1.1 ###
* Show more detailed error message during Bulk Tagging failure

### 1.1.0 ###
* FEATURE: A bulk tagger tags existing images in library
* FEATURE: Under the hood, sends URL to image assets instead of uploading images individually
* FEATURE: Moves Taghound settings to a dedicated screen

### 1.0.4 ###
* BUGFIX: Adds media browser support back to < WordPress 4.7

### 1.0.3 ###
* Removes cruft added to last build.

### 1.0.2 ###
* Updates admin UI to support WordPress 4.7
* Adds a minimum required PHP version of 5.5.

### 1.0.1 ###
* FEATURE: See Clarifai API usage data under Media settings.

### 1.0.0 ###
* Taghound Media Tagger.

