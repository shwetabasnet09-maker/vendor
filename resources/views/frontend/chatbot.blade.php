<!DOCTYPE html>
<html>
<head>
    <title>BotMan Chat</title>
    <script src='https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js'></script>
</head>
<body>
    <script>
        var botmanWidget = {
            frameEndpoint: '{{ route("botman.handle") }}',
            title: 'My Chatbot',
            introMessage: 'Hello! Ask me anything...',
            mainColor: '#007bff',
            bubbleBackground: '#007bff',
            aboutText: '',
        };
    </script>
</body>
</html>
