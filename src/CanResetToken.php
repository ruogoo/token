<?php
/**
 * This file is part of ruogu.
 *
 * Created by HyanCat.
 *
 * Copyright (C) HyanCat. All rights reserved.
 */

namespace Ruogu\Token;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Ruogu\Foundation\TokenRepository\TokenResetable;

Trait CanResetToken
{
    /**
     * The hashing key.
     * @var string
     */
    protected $hashKey = '';

    /**
     * The number of seconds a token should last.
     * @var int
     */
    protected $expires = 30;

    /**
     * Create a new token record.
     * @param TokenResetable $receiver
     * @param array          $options
     * @return string
     */
    public function create(TokenResetable $receiver, array $options = [])
    {
        $this->deleteExisting($receiver);

        $token = $this->generateNewToken();

        Cache::put($receiver->tokenKey(), $this->getPayload($token, $options), $this->expires);

        return $token;
    }

    /**
     * Find a token record or create it if not found.
     * @param \Ruogu\Foundation\TokenRepository\TokenResetable $receiver
     * @param array                                            $options
     * @return string
     */
    public function findOrCreate(TokenResetable $receiver, array $options = [])
    {
        if (Cache::has($receiver->tokenKey())) {
            return Cache::get($receiver->tokenKey())['token'];
        } else {
            return $this->create($receiver, $options);
        }
    }

    /**
     * Determine if the receiver's token record exists and is valid.
     * @param TokenResetable $receiver
     * @param string         $token
     * @return bool
     */
    public function exists(TokenResetable $receiver, string $token)
    {
        if (Cache::has($receiver->tokenKey())) {
            $cachedObject = Cache::get($receiver->tokenKey());

            return $cachedObject['token'] === $token;
        }

        return false;
    }

    /**
     * Destroy a receiver's token record.
     * @param TokenResetable $receiver
     */
    public function destroy(TokenResetable $receiver)
    {
        $this->deleteExisting($receiver);
    }

    /**
     * Get the created time for the receiver.
     * @param \Ruogu\Foundation\TokenRepository\TokenResetable $receiver
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function createdAt(TokenResetable $receiver)
    {
        $cachedObject = Cache::get($receiver->tokenKey());

        return Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $cachedObject['created_at']);
    }

    /**
     * Get the options fields for the receiver.
     * @param \Ruogu\Foundation\TokenRepository\TokenResetable $receiver
     * @return mixed
     */
    public function getOptions(TokenResetable $receiver)
    {
        $cachedObject = Cache::get($receiver->tokenKey());

        return $cachedObject['options'];
    }

    protected function generateNewToken()
    {
        return hash_hmac('sha256', Str::random(40), $this->hashKey);
    }

    protected function getPayload(string $token, array $options)
    {
        return [
            'token'      => $token,
            'options'    => $options,
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
    }

    private function deleteExisting(TokenResetable $receiver)
    {
        return Cache::forget($receiver->tokenKey());
    }
}
