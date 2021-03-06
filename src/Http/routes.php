<?php

$router->group([
    'middleware' => 'auth',
    'namespace' => 'Views',
], function ($router) {
    $router->get('volumes/{id}/maia', [
        'as' => 'volumes-maia',
        'uses' => 'MaiaJobController@index',
    ]);

    $router->get('maia/{id}', [
        'as' => 'maia',
        'uses' => 'MaiaJobController@show',
    ]);
});

$router->group([
    'middleware' => 'auth:web,api',
    'namespace' => 'Api',
    'prefix' => 'api/v1',
], function ($router) {
    $router->resource('volumes/{id}/maia-jobs', 'MaiaJobController', [
        'only' => ['store'],
        'parameters' => ['volumes' => 'id'],
    ]);

    $router->resource('maia-jobs', 'MaiaJobController', [
        'only' => ['destroy'],
        'parameters' => ['maia-jobs' => 'id'],
    ]);

    $router->get('maia-jobs/{id}/training-proposals', 'TrainingProposalController@index');
    $router->post('maia-jobs/{id}/training-proposals', 'TrainingProposalController@submit');
    $router->put('maia/training-proposals/{id}', 'TrainingProposalController@update');

    $router->get('maia-jobs/{id}/annotation-candidates', 'AnnotationCandidateController@index');
    $router->post('maia-jobs/{id}/annotation-candidates', 'AnnotationCandidateController@submit');
    $router->put('maia/annotation-candidates/{id}', 'AnnotationCandidateController@update');

    $router->get('maia-jobs/{id}/images/{id2}/training-proposals', 'MaiaJobImagesController@indexTrainingProposals');
    $router->get('maia-jobs/{id}/images/{id2}/annotation-candidates', 'MaiaJobImagesController@indexAnnotationCandidates');
});
