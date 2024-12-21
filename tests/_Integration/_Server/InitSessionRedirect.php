<!Doctype html>
<html lang="en">
<head>
    <?php if ($redirectParam) { ?>
        <title>Welcome!</title>
    <?php } else { ?>
        <title>Forbidden!</title>
    <?php } ?>
</head>
<body>
<?php if (!$redirectParam) { ?>
    <script> window.location.href = 'http://localhost:8000/init_session?redirect=1'; </script>
<?php } else { ?>
    <script> document.cookie = "session=foo"; </script>
<?php } ?>
</body>
</html>
