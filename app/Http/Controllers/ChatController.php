<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Events\ChatSessionStarted;
use App\Events\NewMessage;
use App\Http\Requests\InitiateChatRequest;
use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Resources\ChatMessageResource;
use App\Http\Resources\ChatSessionResource;
use App\Models\ChatAgent;
use App\Models\ChatSession;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\NewChatMessage;
use App\Notifications\NewChatSession;
use Illuminate\Http\UploadedFile;

class ChatController extends Controller
{
    public function storeMessage(StoreChatMessageRequest $request, ChatSession $session)
    {
        $message = $this->storeMessageVisitor(
            $session,
            $request->message,
            $request->file('attachment'),
            $request->visitor_id // Pass the visitor ID
        );
        // return $message;
        if ($session->agent) {
            $session->agent->user->notify(new NewChatMessage($message));
        }

        return new ChatMessageResource($message);

        // return response()->json([
        //     'message' => 'Message sent successfully',
        //     'data' => $message
        // ]);
    }

    public function storeMessageVisitor(ChatSession $session, string $message, ?UploadedFile $attachment = null, $visitorId)
    {
        $senderType = auth()->check() ? ChatAgent::class : 'visitor';
        $senderId = auth()->check() ? 1 : $visitorId;
    
        // Create a new chat message
        $chatMessage = $session->messages()->create([
            'message' => $message,
            'sender_type' => $senderType,
            'sender_id' => $senderId,
        ]);
    
        if ($attachment) {
            $path = $attachment->store('chat-attachments');
            $chatMessage->update([
                'attachment_path' => $path,
                'attachment_type' => $attachment->getMimeType()
            ]);
        }
    
        // Broadcast the chat message event
        broadcast(new ChatMessageSent($chatMessage))->toOthers();
    
        return $chatMessage;
    }

    
}
