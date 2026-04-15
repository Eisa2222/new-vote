<?php

declare(strict_types=1);

namespace App\Modules\Voting\Exceptions;

final class VotingException extends \RuntimeException
{
    public function render($request)
    {
        $payload = ['message' => $this->getMessage()];
        return $request->expectsJson()
            ? response()->json($payload, 422)
            : back()->withErrors(['voting' => $this->getMessage()]);
    }
}
