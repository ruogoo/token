<?php
/**
 * This file is part of ruogu.
 *
 * Created by HyanCat.
 *
 * Copyright (C) HyanCat. All rights reserved.
 */

namespace Ruogu\Token;

use Ruogu\Foundation\TokenRepository\TokenInterface;

class TokenManager implements TokenInterface
{
    use CanResetToken;

    /**
     * TokenManager constructor.
     * @param     $hashKey
     * @param int $expires
     */
    public function __construct($hashKey, $expires = 60)
    {
        $this->hashKey = $hashKey;
        $this->expires = $expires;
    }
}
