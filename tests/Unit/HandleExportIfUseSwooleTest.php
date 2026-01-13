<?php

declare(strict_types=1);

use Aoding9\Dcat\Xlswriter\Export\HandleExportIfUseSwoole;

describe('HandleExportIfUseSwoole trait', function () {
    it('trait exists and can be used', function () {
        expect(trait_exists(HandleExportIfUseSwoole::class))->toBeTrue();
    });

    it('trait has index method', function () {
        $reflection = new ReflectionClass(HandleExportIfUseSwoole::class);
        $methods = $reflection->getMethods();
        $methodNames = array_map(fn ($m) => $m->getName(), $methods);

        expect($methodNames)->toContain('index');
    });
});
