<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>StockDivs</title>
        <script>
            (() => {
                try {
                    const theme = localStorage.getItem('stockdivs_theme') || 'light';
                    document.documentElement.dataset.theme = theme;
                } catch {
                    document.documentElement.dataset.theme = 'light';
                }
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    </head>
    <body>
        <div id="root"></div>
    </body>
</html>
