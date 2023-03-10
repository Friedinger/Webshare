# Webshare

Webshare: A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

## Features

-   Share files with an easy to remember, custom URL
-   Shorten every URL (custom service like Bitly)
-   Set expiration dates for shares (deleted on first attempt post expiration)
-   Store files in a custom directory on the webserver
-   Store share data in a MySQL database
-   Show existing shares in admin page

## Installation

1. Download the files from the repository and upload them to the webserver.
2. Move the file to the correct location:
    - The files in the _home_ directory must be inside the **root directory** of the webserver. They can be moved into a subdirectory.
    - The _webshare_ directory must be **in the parent directory** of the root directory of the webserver.
3. Create MySQL table for Webshare:

    It should have the following structure:
    | name | type | null | default |
    | ---- | ---- | ---- | ---- |
    | uri | varchar(255) | no | none |
    | file | varchar(255) | yes | NULL |
    | link | varchar(255) | yes | NULL |
    | password | varchar(255) | yes | NULL |
    | expireDate | timestamp | yes | NULL |
    | createDate | timestamp | no | current_timestamp() |

    Make _uri_ a primary key index to ensure unique short links.

4. Adjust config file:
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
The messages displayed after submitting the form can be customized in the Webshare configuration.

```php
<?php print($message) ?>
```

To display the list of existing shares, a table must be added to the admin page. The php script outputs the table data.

```html
<table>
	<th>URI</th>
	<th>File</th>
	<th>Link</th>
	<th>Password</th>
	<th>Expire Date</th>
	<th>Create Date</th>
	<?php print($shareList) ?>
</table>
```

A sample admin page can be found [here](/webshare/adminPage_sample.php).

### View page

The view page must include an iframe to preview the shared file. Therefore, the source must be included with PHP.

```html
<iframe
	src="<?php print($iframeSrc) ?>?action=show"
	title="<?php print($iframeTitle) ?>">
</iframe>
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
<?php print($message) ?>
```

A sample password page can be found [here](/webshare/passwordPage_sample.php).

### Error 404 page

The error 404 page has no required parts but it should inform the user that an error 404 occurred.

A sample error 404 page can be found [here](/webshare/404Page_sample.php).

## Credit and License

Webshare is developed by [Friedinger](https://friedinger.org "friedinger.org").

You can use webshare for free, private and commercial, but you have to include the credit of the developer.

(And it would be kind if you would inform me that you use my project, so that I know it is used :D)
