# Webshare

Webshare: A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

Version 2.0

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
    - The files in the _home_ directory must be **inside the root directory** of the webserver. They can be moved into a subdirectory and the content of the _index.php_ file can also be executed by another custom file for advanced usage. The _.htaccess_ file just redirects all requests to the _index.php_ file.
    - The _webshare_ directory must be **outside directory** of the root directory of the webserver to prevent direct access. You can move the files to your own destination, only the files inside the _function_ directory should remain together in one directory.
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

    For an easy installation you can use the _install.sql_ file provided with webshare.

5. Adjust the _index.php_ file: Set the require paths to the _Webshare.php_ and _webshareConfig.php_ files so that they get loaded properly.
6. Adjust config file:
    - Set the webshare install path when you moved the _index.php_ file into a subdirectory of root directory.
    - Set path to file storage.
    - Set path to _admin_, _view_, _password_ and _delete_ page.
    - Set the database login information for Webshare.
    - Change the action executed on an error 404.
    - Limit access to admin page by validating the login state (recommended) or use an authentication with an _.htaccess_ file.
    - Change action if admin page was requested but user is not authenticated.

## Customization

To customize your Webshare installation to your personal design, you can replace or modify the default sample pages provided in the _webshare_ directory. There are just some elements the pages must offer.

### General outputs

The `Webshare\Output` object provides a variety of information about the current share:

-   `Webshare\Output::$uri`: The uri of the share
-   `Webshare\Output::$value`: The value of the share, either the link or the filename. Should not be used in password page, because it provides information without entering the password.
-   `Webshare\Output::$expireDate`: The timestamp when the share will expire
-   `Webshare\Output::$createDate`: The timestamp of the share creation time
-   `Webshare\Output::$status`: Status information about the current share action, can only be used on some pages.
-   `Webshare\Output::link($uri, $text, $longLink)`: Function to output a link to an URI in webshare, for example the admin page. _$uri_ sets the URI to link to, _$text_ adjusts the text that is visible (default is the link itself), $longLink controls wether the link should just be the URI / text or an complete link with hostname and option to copy it.

### Admin page

The admin page must offer a form to add a share which consists of the following parts:

```html
<form method="post" enctype="multipart/form-data">
	<input type="text" name="uri" pattern="[a-z0-9_-]+" required /><br />
	<input type="file" name="file" /><br />
	<input type="text" name="link" /><br />
	<input type="text" name="password" /><br />
	<input type="datetime-local" name="expireDate" /><br />
	<input type="submit" name="submit" /><br />
</form>
```

To display messages after attempting to add a share you can use the status provided with php in the `Webshare\Output::$status` variable.
It can have the following values, check out sample admin page for an example of handling these values:

-   `success`: The share was successfully added
-   `errorUri`: Share adding failed because the uri is already in use
-   `errorBoth`: Share adding failed because file and link were offered and not just one of them
-   `errorUploadSize`: Share adding failed because upload size limit was exceeded
-   `error`: Share adding failed for another reason

To display the list of existing shares, a table must be added to the admin page. The output object and its variable _$shareList_ output the table data, the links allow sorting the table by the different columns.

```html
<table>
	<th><a href="?sort=uri">URI</a></th>
	<th><a href="?sort=type">Type</a></th>
	<th><a href="?sort=value">Value</a></th>
	<th><a href="?sort=password">Password</a></th>
	<th><a href="?sort=expireDate">Expire Date</a></th>
	<th><a href="?sort=createDate">Create Date</a></th>
	<th>Action</th>
	<?= Webshare\Output::$shareList ?>
</table>
```

A sample admin page can be found [here](/webshare/adminPage_sample.php).

### View page

The view page should display an preview of the requested file. Therefore the following php code must be included to output the preview.

```php
<?= Webshare\Output::$sharePreview ?>
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

The status of the password access is provided by the `Webshare\Output::$status` variable. Its value is set to `incorrect` if the entered password is not correct. If the password matches the user is directly redirected to the share without displaying any message.

A sample password page can be found [here](/webshare/passwordPage_sample.php).

### Delete page

The delete page must include a form to confirm the deletion of a share. For that reason, a the following form should be used:

```html
<form method="post">
	<input type="hidden" name="share" value="<?= Webshare\Output::$uri ?>" />
	<input type="submit" name="submit" value="Delete" /><br />
</form>
```

To inform the user about a successful deletion, the status of the deletion is provided by the `Webshare\Output::$status` variable. It can have the following values:

-   `success`: The share was successfully deleted
-   `error`: An error occurred during the deletion
-   Something else or unset: Default output before confirming the deletion

A sample password page can be found [here](/webshare/deletePage_sample.php).

## Credit and license

© 2023
[Friedinger](https://friedinger.org "friedinger.org")

License: [MIT License
](https://github.com/Friedinger/Webshare/blob/main/LICENSE)
