<!Doctype html>
<html lang="en">
<head>
    <title>Another example page for taking a screenshot!</title>
</head>
<body style="background-color: cadetblue;">
<h1>Hi On Page <?=$page?></h1>

<?php if ($page === '0') { ?>
<a href="/crawl-screenshot/1">Link to another page</a>
<?php } ?>
</body>
</html>
