<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional;

trait GetJwtTokenTrait
{
    /**
     * @return non-empty-string
     */
    public function getJwtToken(): string
    {
        return 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.' .
            'eyJlbWFpbCI6InVzZXJAZXhhbXBsZS5jb20iLCJzdWIiOiIwMUZQWkdIQUc2NUUwTjlBUldHNlkxUkgzNCIsImF1ZCI6WyJhcGkiXX0.' .
            'hMGV5MJexFIDIuh5gsqkhJ7C3SDQGnOW7sZVS5b6X08';
    }
}
