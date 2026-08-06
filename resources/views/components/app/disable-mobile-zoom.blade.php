<script>
    (() => {
        const content = 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no';
        const meta = document.querySelector('meta[name="viewport"]');

        if (meta) {
            meta.setAttribute('content', content);
            return;
        }

        const created = document.createElement('meta');
        created.name = 'viewport';
        created.content = content;
        document.head.appendChild(created);
    })();
</script>
