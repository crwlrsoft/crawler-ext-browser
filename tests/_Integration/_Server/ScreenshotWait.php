<!Doctype html>
<html lang="en">
<head>
    <title>Example page for wait time before taking screenshot</title>
</head>
<body>
<div id="test"></div>
<script>
    window.setTimeout(function () {
        const element = document.getElementById('test');
        element.style.width = '1920px';
        element.style.height = '1000px';
        element.style.backgroundColor = 'red';
    }, 1500);
</script>
</body>
</html>
