<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatSession;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Auth;
use App\Events\NewMessage;

class AgentController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $chatSessions = auth()->user()->chats()->with('visitor')->latest()->paginate(20);
        // return $agent;
        // $sessions = $agent->chatSessions()
        //     ->with('messages')
        //     ->latest()
        //     ->paginate(20);

        return view('agent', compact('chatSessions'));
    }

    public function show(Chat $chat)
    {
        // return $chat;
        // $this->authorize('view', $session);
        // $session->load('messages'); , 'messages.sender'
        $chat->load(['visitor','messages']);
        // return $chat;
        return view('agent-show', compact('chat'));
    }

    // public function storeMessage(Request $request, ChatSession $session)
    // {
    //     $this->authorize('reply', $session);

    //     $message = $this->chatService->storeMessage(
    //         $session,
    //         $request->message,
    //         $request->file('attachment')
    //     );

    //     return response()->json([
    //         'message' => 'Message sent successfully',
    //         'data' => $message
    //     ]);
    // }

    // public function updateStatus(Request $request)
    // {
    //     $agent = auth()->user()->chatAgent;
    //     $this->chatService->updateAgentStatus($agent, $request->status);

    //     return response()->json(['status' => 'updated']);
    // }

    // public function endSession(ChatSession $session)
    // {
    //     $this->authorize('end', $session);
    //     $this->chatService->endSession($session);

    //     return redirect()->route('agent.chat.index')
    //     ->with('success', 'Chat session ended successfully');
    // }

    public function sendMessageAsAgent(Request $request)
    {
        // Validate the request data
        $request->validate([
            'chat_id' => 'required|exists:chats,id', // Ensure the chat exists
            'message' => 'required|string',         // Ensure the message is a string
        ]);
    
        // Retrieve the chat and ensure the agent is authorized to send a message
        $chat = Chat::findOrFail($request->chat_id);
    
        // Optional: Add check to ensure the logged-in user is the assigned agent for the chat
        if ($chat->agent_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to send messages in this chat.',
            ], 403);
        }
    
        // Save the message
        $message = Message::create([
            'chat_id' => $request->chat_id,
            'sender_type' => 'agent',
            'message' => $request->message,
        ]);
    
        // Log the action for monitoring
        \Log::info('Agent sent a message', [
            'chat_id' => $chat->id,
            'agent_id' => $chat->agent_id,
            'message' => $request->message,
        ]);
    
        // Broadcast the message to the chat channel
        broadcast(new NewMessage($message->message, $request->chat_id))->toOthers();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Message sent successfully.',
            'data' => [
                'message' => $message->message,
                'sender' => 'Agent',
                'chat_id' => $request->chat_id,
            ],
        ]);
    }
}
