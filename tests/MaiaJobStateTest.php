<?php

namespace Biigle\Tests\Modules\Maia;

use Biigle\Modules\Maia\MaiaJobState;
use ModelTestCase;

class MaiaJobStateTest extends ModelTestCase
{
    /**
     * The model class this class will test.
     */
    protected static $modelClass = MaiaJobState::class;

    public function testAttributes()
    {
        $this->assertNotNull($this->model->name);
        $this->assertNull($this->model->created_at);
        $this->assertNull($this->model->updated_at);
    }

    public function testNoveltyDetection()
    {
        $this->assertNotNull(MaiaJobState::noveltyDetection());
    }

    public function testFailedNoveltyDetection()
    {
        $this->assertNotNull(MaiaJobState::failedNoveltyDetection());
    }

    public function testTrainingProposals()
    {
        $this->assertNotNull(MaiaJobState::trainingProposals());
    }

    public function testInstanceSegmentation()
    {
        $this->assertNotNull(MaiaJobState::instanceSegmentation());
    }

    public function testFailedInstanceSegmentation()
    {
        $this->assertNotNull(MaiaJobState::failedInstanceSegmentation());
    }

    public function testAnnotationCandidates()
    {
        $this->assertNotNull(MaiaJobState::annotationCandidates());
    }
}
