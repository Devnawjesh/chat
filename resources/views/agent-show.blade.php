@extends('layouts.app')

@section('content')
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Chat with {{ $chat->visitor->name }}
                    <span class="badge bg-{{ $chat->status === 'active' ? 'success' : 'warning text-dark' }} ms-2">
                        {{ ucfirst($chat->status) }}
                    </span>
                </h3>
            </div>
            <div class="card-body chat-container" id="chat-messages">
                @foreach ($chat->messages as $message)
                    <div class="chat-message p-3 rounded {{ $message->sender_type === 'agent' ? 'agent' : 'visitor' }}">
                        <div class="text-muted mb-1">
                            {{ $message->sender_type === 'agent' ? 'You' : $chat->visitor->name }}
                            - {{ $message->created_at->diffForHumans() }}
                        </div>
                        <div class="message-text">{{ $message->message }}</div>
                    </div>
                @endforeach
            </div>

            @if ($chat->status === 'active')
                <div class="card-footer">
                    <form id="message-form" class="d-flex gap-2">
                        <input type="text" id="message" class="form-control" placeholder="Type your message..." autocomplete="off">
                        <button type="button" id="agent-send-message" class="btn btn-primary">Send</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Pass necessary data to JavaScript -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const agentId = {{ auth()->user()->id }}; // Logged-in agent's ID
            const chatId = {{ $chat->id }}; // Chat ID

            // Initialize Pusher
            const pusher = new Pusher("73fd4859f7ea3b680067", {
                cluster: "ap1",
                encrypted: true
            });

            // Subscribe to the chat channel
            const channel = pusher.subscribe('chat.' + chatId);

            // Listen for new messages
            channel.bind('message.sent', function(data) {
                console.log('New message received:', data.message);
                appendMessage(data.message, 'visitor'); // Show received message
            });

            // Send message to visitor
            document.getElementById('agent-send-message').addEventListener('click', function () {
                const messageInput = document.getElementById('message');
                const message = messageInput.value.trim();

                if (message === '') return; // Prevent empty messages

                fetch('/agent/send-message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ chat_id: chatId, message }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        appendMessage(message, 'agent'); // Show sent message
                        messageInput.value = ''; // Clear input field
                    }
                })
                .catch(error => console.error('Error sending message:', error));
            });

            // Function to append message to chat
            function appendMessage(message, senderType) {
                const chatMessages = document.getElementById('chat-messages');
                const messageElement = document.createElement('div');
                messageElement.classList.add('chat-message', 'p-3', 'rounded', senderType);
                messageElement.innerHTML = `
                    <div class="text-muted mb-1">
                        ${senderType === 'agent' ? 'You' : '{{ $chat->visitor->name }}'} - Just now
                    </div>
                    <div class="message-text">${message}</div>
                `;
                chatMessages.appendChild(messageElement);
                chatMessages.scrollTop = chatMessages.scrollHeight; // Auto-scroll to latest message
            }
        });
    </script>
@endsection
