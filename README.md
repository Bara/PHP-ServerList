# PHP-ServerList

One way to add this with iframe + auto sizing:
---
```html
<script>
    function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
    }
</script>
<iframe src="/serverviewer/" seamless="false" style="border:none;" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)" align="left"></iframe>```

Example cron entry:
---
`php /path/to/your/bot/files/bot.php > /path/to/your/log/folder/output.txt`
or just use if you don't want an output file
`php /path/to/your/bot/files/bot.php > /dev/null`
