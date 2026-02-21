<?php

use App\Models\Evento;

it('uses slug as route key and has slug fillable', function () {
    $model = new Evento;

    expect($model->getRouteKeyName())->toBe('slug');
    expect(in_array('slug', $model->getFillable()))->toBeTrue();
});
