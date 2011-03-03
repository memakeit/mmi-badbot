# MMI BadBot

## Setup

For this module to function properly, the following steps need to be completed.

1.	Create the database table using the `tables.sql` script (located in
	the module's `sql` directory).

2.	Update robots.txt by adding the following disallow directive:
		
		Disallow: /mmi/badbot/
		
	An example `robots.txt` file is located in the module root.

3.	Copy the `badbot.css` CSS file from the module's `media/css` directory to 
	your application's CSS directory. 

	If your application's CSS directory is not `media/css`, update the CSS paths 
	in the following templates:
		
	* `denied.mustache`
	* `trap.mustache` 

4.	Copy the module's `mmi-badbot.php` config file to your application's `config`
	directory. Update the application's config file.

	* Set the `to` and `from` email addresses
	* Set your Twitter username (`twitter_username`)

5.	Add a link to the bad bot trap. This can be done site-wide (in a template), 
	or you may wish to include the trap on specific pages. The PHP is:
	
		$trap_link = Kostache::factory('mmi/badbot/link')->render();
		
	Either `echo $trap_link;`, or set a template variable to `$trap_link`.

## Configuration

The configuration file is named `mmi-badbot.php`.
