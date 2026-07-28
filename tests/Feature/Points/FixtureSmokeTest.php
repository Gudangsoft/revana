<?php

namespace Tests\Feature\Points;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixtureSmokeTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPointTestFixtures;

    public function test_fixtures_can_be_created(): void
    {
        $submission = $this->makeSubmission();
        $pic = $this->makePic();
        $marketing = $this->makeMarketing();

        $this->assertNotNull($submission->id);
        $this->assertNotNull($pic->id);
        $this->assertNotNull($marketing->id);
    }
}
