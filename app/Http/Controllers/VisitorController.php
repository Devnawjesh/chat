<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Models\User;
use App\Models\Visitor;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VisitorController extends Controller
{
    /**
     * Assign an available agent to the chat.
     */
    private function assignAvailableAgent($visitorId)
    {
        // Find the first available agent who is online and not currently engaged in an active chat
        $agent = User::where('is_online', true)
            ->whereDoesntHave('chats', function ($query) {
                $query->where('is_active', 1);
            })
            ->role('Agent')
            ->first();

        if ($agent) {
            // Assign the agent to the chat
            $chat = Chat::create([
                'visitor_id' => $visitorId,
                'agent_id' => $agent->id,
                'status' => 'active',
            ]);

            // Log agent assignment
            Log::info('Assigned agent to visitor', [
                'visitor_id' => $visitorId,
                'agent_id' => $agent->id,
                'chat_id' => $chat->id,
            ]);

            return $chat;
        }

        // Log when no agent is available
        Log::warning('No available agent for visitor', [
            'visitor_id' => $visitorId,
        ]);

        return null;
    }

    /**
     * Start a chat session for the visitor.
     */
    public function startChat(Request $request)
    {
        $request->validate([
            'name' => 'required_if:user_id,null',
            'email' => 'required_if:user_id,null|email',
            'contact' => 'required_if:user_id,null',
        ]);

        // Check if the user is logged in
        $userId = Auth::id();
        // Create or find the visitor
        if ($userId) {
            // Logged-in user
            $visitor = Visitor::firstOrCreate(
                ['user_id' => $userId],
                [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'contact' => 'N/A', // Or fetch from user profile
                ]
            );
        } else {
            // Guest visitor
            $visitor = Visitor::create($request->only('name', 'email', 'contact'));
        }

        // Try to assign an available agent
        $retryCount = config('chat.retry_count', 3); // Number of retries from config
        $retryDelay = config('chat.retry_delay', 5); // Delay in seconds from config

        for ($i = 0; $i < $retryCount; $i++) {
            $chat = $this->assignAvailableAgent($visitor->id);

            if ($chat) {
                // Store chat_id in session
                $request->session()->put('chat_id', $chat->id);
                $request->session()->save(); // Force immediate session save

                Log::info('Chat session started', [
                    'session_id' => session()->getId(),
                    'chat_id' => session('chat_id'),
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Agent assigned successfully.',
                    'chat_id' => $chat->id,
                    'agent_id' => $chat->agent_id,
                ]);
            }

            // Wait for a few seconds before retrying
            // Log::info("Retry attempt #{$i+1} for visitor {$visitor->id} to assign agent.");
            sleep($retryDelay);
        }

        // If no agent is available after retries
        Log::warning('No agents available after retries', [
            'visitor_id' => $visitor->id,
        ]);

        return response()->json([
            'status' => 'waiting',
            'message' => 'No agents are available at the moment. Please wait or try again later.',
        ]);
    }

    /**
     * Send a message from the visitor to the agent.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string',
        ]);

        // Ensure the chat belongs to the visitor or the logged-in user
        if ($request->chat_id != session('chat_id')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized chat session']);
        }

        // Save the message
        $message = Message::create([
            'chat_id' => $request->chat_id,
            'sender_type' => 'visitor',
            'message' => $request->message,
        ]);

        Log::info('Visitor sent a message', [
            'chat_id' => $request->chat_id,
            'message' => $request->message,
            'visitor_id' => session('visitor_id'), // If needed
        ]);

        // Broadcast the message to the chat channel
        broadcast(new NewMessage($message->message, $request->chat_id))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => $message->message,
        ]);
    }
}
