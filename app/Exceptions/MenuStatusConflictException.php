<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MenuStatusConflictException extends Exception
{
    public function __construct(string $message = 'Status menu sudah berubah, muat ulang halaman')
    {
        parent::__construct($message, 409);
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 409);
        }

        return back()
            ->withInput()
            ->withErrors(['status' => $this->getMessage()]);
    }
}
