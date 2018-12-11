<?php

namespace Biigle\Modules\Maia\Http\Controllers\Api;

use Queue;
use Biigle\Volume;
use Biigle\Modules\Maia\MaiaJob;
use Biigle\Http\Controllers\Api\Controller;
use Biigle\Modules\Maia\MaiaJobState as State;
use Biigle\Modules\Maia\Http\Requests\StoreMaiaJob;
use Biigle\Modules\Maia\Http\Requests\DestroyMaiaJob;

class MaiaJobController extends Controller
{
    /**
     * Creates a new MAIA job for the specified volume.
     *
     * @api {post} volumes/:id/maia-jobs Create a new MAIA job
     * @apiGroup Maia
     * @apiName StoreMaiaJob
     * @apiPermission projectEditor
     * @apiDescription New MAIA jobs cannot be created for volumes with very large (tiled) images.
     *
     * @apiParam {Number} id The volume ID.
     *
     * @apiParam (Required attributes) {number} nd_clusters Number of different kinds of images to expect. Images are of the same kind if they have similar lighting conditions or show similar patterns (e.g. sea floor, habitat types). Increase this number if you expect many different kinds of images. Lower the number to 1 if you have very few images and/or the content is largely uniform.
     * @apiParam (Required attributes) {number} nd_patch_size Size in pixels of the image patches used determine the training proposals. Increase the size if the images contain larger objects of interest, decrease the size if the objects are smaller. Larger patch sizes take longer to compute. Must be an odd number.
     * @apiParam (Required attributes) {number} nd_threshold Percentile of pixel saliency values used to determine the saliency threshold. Lower this value to get more training proposals. The default value should be fine for most cases.
     * @apiParam (Required attributes) {number} nd_latent_size Learning capability used to determine training proposals. Increase this number to ignore more complex objects and patterns.
     * @apiParam (Required attributes) {number} nd_trainset_size Number of training image patches used to determine training proposals. You can increase this number for a large volume but it will take longer to compute.
     * @apiParam (Required attributes) {number} nd_epochs Time spent on training when determining the training proposals.
     * @apiParam (Required attributes) {number} nd_stride A higher stride increases the speed of the novelty detection but reduces the sensitivity to small regions or objects.
     * @apiParam (Required attributes) {number} nd_ignore_radius Ignore training proposals or annotation candidates which have a radius smaller or equal than this value in pixels.
     * @apiParam (Required attributes) {number} is_epochs_head Time spent on training only the head layers of Mask R-CNN for instance segmentation.
     * @apiParam (Required attributes) {number} is_epochs_all Time spent on training  all layers of Mask R-CNN for instance segmentation.
     *
     * @param StoreMaiaJob $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMaiaJob $request)
    {
        $job = new MaiaJob;
        $job->volume_id = $request->volume->id;
        $job->user_id = $request->user()->id;
        $job->state_id = State::noveltyDetectionId();
        $job->params = $request->only([
            // nd_* are parameters for novelty detection, is_* for instance segmentation.
            'nd_clusters',
            'nd_patch_size',
            'nd_threshold',
            'nd_latent_size',
            'nd_trainset_size',
            'nd_epochs',
            'nd_stride',
            'nd_ignore_radius',
            'is_epochs_head',
            'is_epochs_all',
        ]);
        $job->save();

        if ($this->isAutomatedRequest()) {
            return $job;
        }

        return $this->fuzzyRedirect('maia', $job->id);
    }

    /**
     * Delete a MAIA job.
     *
     * @api {delete} maia-jobs/:id Delete a MAIA job
     * @apiGroup Maia
     * @apiName DestroyMaiaJob
     * @apiPermission projectEditor
     *
     * @apiParam {Number} id The job ID.
     *
     * @param DestroyMaiaJob $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(DestroyMaiaJob $request)
    {
        $volumeId = $request->job->volume_id;
        $request->job->delete();

        if (!$this->isAutomatedRequest()) {
            return $this->fuzzyRedirect('volumes-maia', $volumeId)
                ->with('message', 'Job deleted')
                ->with('messageType', 'success');
        }
    }
}
