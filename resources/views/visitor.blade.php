@extends('layouts.app')

@section('content')
<div class="container">
    <div id="chat-widget" class="chat-widget">
        {{-- <div class="chat-widget-button" onclick="toggleChatBox()">
            <i class="fas fa-comments"></i>
        </div> --}}
        <div class="chat-widget-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" fill="none"
                class="fs-4">
                <rect width="40" height="40" rx="20" fill="" />
                <path
                    d="M20 10C14.477 10 10 13.805 10 18.5C10 20.647 10.969 22.648 12.641 24.162C12.339 25.223 11.732 27.238 11.343 28.387C11.251 28.657 11.63 28.893 11.861 28.709C13.171 27.64 15.394 26.035 16.472 25.383C17.62 25.788 18.779 26 20 26C25.523 26 30 22.195 30 17.5C30 12.805 25.523 10 20 10Z"
                    fill="#FFFFFF" />
            </svg>
        </div>
        <div class="chat-widget-box" >
            <div class="chat-widget-header">
                <h4>Chat Support</h4>
                {{-- <button onclick="toggleChatBox()" class="close-btn">&times;</button> --}}
                <button class="close-btn">&times;</button>
            </div>
            <div id="chat-messages" class="chat-widget-messages"></div>
            <div id="chat-form" class="chat-widget-form">
                <form id="initial-form">
                    <div class="form-group mb-3">
                        <input type="text" name="visitor_name" class="form-control" placeholder="Your Name" required>
                    </div>
                    <div class="form-group mb-3">
                        <input type="tel" name="visitor_phone" class="form-control" placeholder="Your Phone" required>
                    </div>
                    <div class="form-group mb-3">
                        <input type="email" name="visitor_email" class="form-control" placeholder="Your Email (optional)">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Start Chat</button>
                </form>
            </div>
        </div>
    
        <style>
            .chat-widget {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 1000;
            }
    
            .chat-widget-button {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: #0d6efd;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            }
    
            .chat-widget-box {
                position: fixed;
                bottom: 90px;
                right: 20px;
                width: 350px;
                height: 500px;
                background: white;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                display: flex;
                flex-direction: column;
            }
    
            .chat-widget-header {
                padding: 15px;
                background: #f8f9fa;
                border-radius: 10px 10px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
    
            .chat-widget-messages {
                flex: 1;
                overflow-y: auto;
                padding: 15px;
            }
    
            .chat-widget-form {
                padding: 15px;
                border-top: 1px solid #dee2e6;
            }
        </style>
    </div>
</div>
@endsection
