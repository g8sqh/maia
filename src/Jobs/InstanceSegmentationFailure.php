<?php

namespace Biigle\Modules\Maia\Jobs;

use Biigle\Modules\Maia\MaiaJob;
use Biigle\Modules\Maia\MaiaJobState as State;

class InstanceSegmentationFailure extends JobFailure
{
    /**
     * {@inheritdoc}
     */
    protected function updateJobState(MaiaJob $job)
    {
        $job->state_id = State::failedInstanceSegmentationId();
    }
}