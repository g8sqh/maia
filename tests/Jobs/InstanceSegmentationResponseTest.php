<?php

namespace Biigle\Tests\Modules\Maia\Jobs;

use Biigle\Modules\Largo\Jobs\GenerateAnnotationPatch;
use Biigle\Modules\Maia\Jobs\InstanceSegmentationFailure;
use Biigle\Modules\Maia\Jobs\InstanceSegmentationResponse;
use Biigle\Modules\Maia\MaiaJob;
use Biigle\Modules\Maia\MaiaJobState as State;
use Biigle\Modules\Maia\Notifications\InstanceSegmentationComplete;
use Biigle\Shape;
use Biigle\Tests\ImageTest;
use Biigle\Tests\Modules\Maia\MaiaJobTest;
use Exception;
use Illuminate\Support\Facades\Notification;
use Queue;
use TestCase;

class InstanceSegmentationResponseTest extends TestCase
{
    public function testHandle()
    {
        $job = MaiaJobTest::create(['state_id' => State::instanceSegmentationId()]);
        $image = ImageTest::create(['volume_id' => $job->volume_id]);

        $candidates = [[$image->id, 100, 200, 20, 0]];

        $response = new InstanceSegmentationResponse($job->id, $candidates);
        Queue::fake();
        Notification::fake();
        $response->handle();

        $this->assertEquals(State::annotationCandidatesId(), $job->fresh()->state_id);

        $annotations = $job->annotationCandidates()->get();
        $this->assertEquals(1, $annotations->count());
        $this->assertEquals([100, 200, 20], $annotations[0]->points);
        $this->assertEquals(0, $annotations[0]->score);
        $this->assertNull($annotations[0]->label_id);
        $this->assertNull($annotations[0]->annotation_id);
        $this->assertEquals($image->id, $annotations[0]->image_id);
        $this->assertEquals(Shape::circleId(), $annotations[0]->shape_id);

        Queue::assertPushed(GenerateAnnotationPatch::class);
        Notification::assertSentTo($job->user, InstanceSegmentationComplete::class);
    }

    public function testHandleWrongState()
    {
        $job = MaiaJobTest::create(['state_id' => State::failedInstanceSegmentationId()]);
        $image = ImageTest::create(['volume_id' => $job->volume_id]);

        $candidates = [[$image->id, 100, 200, 20, 0]];

        $response = new InstanceSegmentationResponse($job->id, $candidates);
        Queue::fake();
        $response->handle();

        $this->assertEquals(State::failedInstanceSegmentationId(), $job->fresh()->state_id);

        $this->assertFalse($job->annotationCandidates()->exists());
        Queue::assertNotPushed(GenerateAnnotationPatch::class);
    }

    public function testHandleRollback()
    {
        $job = MaiaJobTest::create(['state_id' => State::instanceSegmentationId()]);
        $image = ImageTest::create(['volume_id' => $job->volume_id]);

        $proposals = [[$image->id, 100, 200, 20, 0.9]];

        $response = new InstanceSegmentationResponseStub($job->id, $proposals);
        try {
            $response->handle();
            $this->assertFalse(true);
        } catch (Exception $e) {
            //
        }

        $this->assertFalse($job->annotationCandidates()->exists());
        $this->assertEquals(State::instanceSegmentationId(), $job->fresh()->state_id);
    }

    public function testFailed()
    {
        $job = MaiaJobTest::create(['state_id' => State::instanceSegmentationId()]);
        $proposals = [[1, 100, 200, 20, 0.9]];

        $response = new InstanceSegmentationResponse($job->id, $proposals);
        Queue::fake();
        $response->failed(new Exception);
        Queue::assertPushed(InstanceSegmentationFailure::class);
    }
}

class InstanceSegmentationResponseStub extends InstanceSegmentationResponse
{
    protected function updateJobState(MaiaJob $job)
    {
        throw new Exception('Something went wrong!');
    }
}
