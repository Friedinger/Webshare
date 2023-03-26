# Webshare

Webshare: A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

## Features

-   Share files with an easy to remember, custom URL
-   Shorten every URL (custom service like Bitly)
-   Set expiration dates for shares (deleted on first attempt post expiration)
-   Store files in a custom directory on the webserver
-   Store share data in a MySQL database
-   Show existing shares in admin page
-   Delete shares from admin page

## Installation

1. Download the source code from the [latest release](https://github.com/Friedinger/Webshare/releases/latest).
2. Unzip the files and upload them to your webserver.
3. Move the file to the correct location:
    - The files in the _home_ directory must be inside the **root directory** of the webserver. They can be moved into a subdirectory.
    - The _webshare_ directory must be **in the parent directory** of the root directory of the webserver.
4. Create MySQL table for Webshare:

    It should have the following structure:
    | name | type | null | default |
    | ---- | ---- | ---- | ---- |
    | uri | varchar(255) | no | none |
    | type | varchar(255) | no | none |
    | value | varchar(255) | no | none |
    | password | varchar(255) | yes | NULL |
    | expireDate | timestamp | yes | NULL |
    | createDate | timestamp | no | current_timestamp() |

    Make _uri_ a primary key index to ensure unique short links.

5. Adjust config file:
    - Set path to file storage.
    - Set path to _admin_, _view_ and _error 404_ page.
    - Set the database login information (_hostname_, _username_, _password_, _database name_ and _table name_ for Webshare).
    - Limit access to admin page by validating the login state (recommended) or use an authentication with an _.htaccess_ file.
    - Change action if admin page was requested but user is not authenticated.

## Customization

Inside the Webshare folder you can customize your Webshare installation to your personal design by replacing or modifying the default sample pages. There are just some elements the pages must offer:

### Admin page

The admin page must offer a form to add a share which consists of the following parts:

```html
<form method="post" enctype="multipart/form-data">
	<input type="text" name="uri" required /><br />
	<input type="file" name="file" /><br />
	<input type="url" name="link" /><br />
	<input type="text" name="password" /><br />
	<input type="datetime-local" name="expireDate" /><br />
	<input type="submit" name="submit" /><br />
</form>
```

The following PHP code should also be included to display messages after attempting to add the share.

```php
<?php
if ($status[0] == "success") { ?>
	// Success message, add the php line below to output a link and a copy icon to the share.
	<?php print($status[1]) ?>
<?php }
if ($status[0] == "errorBoth") { ?>
	// Message if share adding failed due to a provision of file and link input at the same time.
<?php }
if ($status[0] == "errorUri") { ?>
	// Message if share adding failed because the entered URI is already in use.
<?php }
if ($status[0] == "errorUploadSize") { ?>
	// Message if share adding failed due to an excess of the upload size.
<?php }
if ($status[0] == "errorDefault") { ?>
	// Message if share adding failed for another reason.
<?php } ?>
```

To display the list of existing shares, a table must be added to the admin page. The php script outputs the table data.

```html
<table>
	<th>URI</th>
	<th>Type</th>
	<th>Value</th>
	<th>Password</th>
	<th>Expire Date</th>
	<th>Create Date</th>
	<th>Action</th>
	<?php print($shareList) ?>
</table>
```

A sample admin page can be found [here](/webshare/adminPage_sample.php).

### View page

The view page should display an preview of the requested file. Therefore the following php code must be included to output the preview.

```php
<?php print($sharePreview) ?>
```

To display the file name you can use this script:

```php
<?php print($shareFileName) ?>
```

A sample view page can be found [here](/webshare/viewPage_sample.php).

### Password page

The password page must contain a form to enter the password to access the protected share.

```html
<form method="post">
	<label>Password: </label><input type="password" name="password" /><br />
	<input type="submit" value="Submit password" name="submit" /><br />
</form>
```

Furthermore, the following PHP code should be included to display messages, for example if the entered password is incorrect.

```php
<?php if ($status == "incorrect") { ?>
	// Message if the password is incorrect
<?php } else { ?>
	// Default message that requests the user to enter the password
<?php } ?>
```

A sample password page can be found [here](/webshare/passwordPage_sample.php).

### Delete page

The delete page must include a form to confirm the deletion of a share. For that reason, a the following form should be used:

```html
<form method="post">
	<input type="hidden" name="share" value="<?php print($uri) ?>" />
	<input type="submit" name="submit" value="Delete" /><br />
</form>
```

To inform the user about a successful deletion, a messages can be printed after checking the deletion status with this PHP code:

```php
<?php if ($status == "success") { ?>
	// Success message in HTML
<?php } else { ?>
	// Form from above
<?php } ?>
```

### Error 404 page

The error 404 page has no required parts but it should inform the user that an error 404 occurred.

A sample error 404 page can be found [here](/webshare/404Page_sample.php).

## Credit and license

© 2023
[Friedinger](https://friedinger.org "friedinger.org")

License: [MIT License
](https://github.com/Friedinger/Webshare/blob/main/LICENSE)
