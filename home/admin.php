<!DOCTYPE html>
<html>

<body>
    <h2>Upload</h2>
    <form method="post" enctype="multipart/form-data" action="addShare.php">
        URI: <input type="text" name="uri" required><br>
        File: <input type="file" name="file"><br>
        Link: <input type="text" name="link"><br>
        Expire Date: <input type="datetime-local" name="expireDate"><br>
        <input type="submit" value="Submit" name="submit"><br>
    </form>
</body>

</html>