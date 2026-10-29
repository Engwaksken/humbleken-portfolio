<?php

namespace App\Http\Controllers;

use App\Support\AiChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ChatbotController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        abort_unless(AiChat::enabled(), 404);

        $data = Validator::make($request->all(), [
            'messages' => ['required', 'array', 'min:1', 'max:12'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:2000'],
        ])->validate();

        try {
            return response()->json(['reply' => AiChat::reply($data['messages'])]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }
}
