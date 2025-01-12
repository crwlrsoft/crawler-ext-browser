<!Doctype html>
<html lang="en">
<head>
    <title>Infinite Scrolling Example Page</title>
</head>
<body>
<div style="height: 200vh;">
    Hallo
</div>
<script>
    const body = document.body;
    const html = document.documentElement;
    let isCurrentlyAddingMoreContent = false;
    let addedContentElements = 1;

    document.addEventListener("scroll", (event) => {
        const totalDocumentHeight = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );
        const scrollingPosition = window.pageYOffset;
        const viewportHeight = window.innerHeight;

        if (
            !isCurrentlyAddingMoreContent &&
            addedContentElements < 5 &&
            (viewportHeight + scrollingPosition) >= totalDocumentHeight - 300
        ) {
            isCurrentlyAddingMoreContent = true;

            setTimeout(function () {
                const el = document.createElement('div');
                el.style = 'height: 200vh;';
                el.innerHTML = 'Element ' + addedContentElements;
                document.body.appendChild(el);

                isCurrentlyAddingMoreContent = false;
                addedContentElements++;
            }, 500);
        }
    }, false);
</script>
</body>
</html>
