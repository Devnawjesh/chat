@if (Auth::check())
    <!-- Logged-in user -->
    <button id="start-chat">Start Chat</button>
@else
    <!-- Guest visitor -->
    <form id="start-chat-form">
        <input type="text" name="name" placeholder="Your Name" required>
        <input type="email" name="email" placeholder="Your Email" required>
        <input type="text" name="contact" placeholder="Your Contact" required>
        <button type="submit">Start Chat</button>
    </form>
@endif

<div id="chat-status"></div>

<script>
    // Handle logged-in user
    document.getElementById('start-chat')?.addEventListener('click', function () {
        fetch('/visitor/start-chat', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({}),
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('chat-status').innerText = 'Chat started with Agent #' + data.agent_id;
                // Connect to WebSocket and start chatting
            } else if (data.status === 'waiting') {
                document.getElementById('chat-status').innerText = data.message;
            }
        });
    });

    // Handle guest visitor
    document.getElementById('start-chat-form')?.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/visitor/start-chat', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('chat-status').innerText = 'Chat started with Agent #' + data.agent_id;
                // Connect to WebSocket and start chatting
            } else if (data.status === 'waiting') {
                document.getElementById('chat-status').innerText = data.message;
            }
        });
    });
</script>