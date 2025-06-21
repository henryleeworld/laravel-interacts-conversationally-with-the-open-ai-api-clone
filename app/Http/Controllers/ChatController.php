<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use OpenAI\Laravel\Facades\OpenAI;

/**
 * Chat controller to talk with the AI provider
 */
class ChatController extends Controller
{
    /**
     * Show the main chat screen
     */
    public function index(): Application|View|Factory|ApplicationContract
    {
        $messages = collect(session('messages', []))->reject(fn ($message) => $message['role'] === 'system');

        return view('chat', [
            'messages' => $messages
        ]);
    }

    /**
     * Sends a message to GPT API.
     */
    public function store(Request $request): Application|Redirector|RedirectResponse|ApplicationContract
    {
        $messages = $request->session()->get('messages', [
            ['role' => 'system', 'content' => __('You are a ChatGPT clone. Answer as concisely as possible.')]
        ]);

        $messages[] = ['role' => 'user', 'content' => $request->input('message')];
        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages
        ]);
        $messages[] = ['role' => 'assistant', 'content' => $response->choices[0]->message->content];
        $request->session()->put('messages', $messages);

        return redirect('/');
    }

    /**
     * Reset the session and deletes the messages.
     */
    public function destroy(Request $request): Application|Redirector|RedirectResponse|ApplicationContract
    {
        $request->session()->forget('messages');

        return redirect('/');
    }
}
