<?php

namespace Tests\Unit;

use App\Support\UserImages;
use Tests\TestCase;

class UserImagesTest extends TestCase
{
    public function test_variant_path_is_next_to_the_original(): void
    {
        $this->assertSame('images/users/user_5/w480/abc.jpg', UserImages::variantPath('images/users/user_5/abc.PNG', 480));
    }

    public function test_foreign_and_missing_urls_are_returned_unchanged(): void
    {
        $this->assertSame('https://example.com/a.jpg', UserImages::url('https://example.com/a.jpg', 480));
        $this->assertSame('/storage/images/users/user_1/missing.jpg', UserImages::url('/storage/images/users/user_1/missing.jpg', 480));
        $this->assertNull(UserImages::url(null, 480));
    }
}
