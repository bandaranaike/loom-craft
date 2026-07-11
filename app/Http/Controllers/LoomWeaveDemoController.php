<?php

namespace App\Http\Controllers;

use App\Support\Site;
use Inertia\Inertia;
use Inertia\Response;

class LoomWeaveDemoController extends Controller
{
    public function __invoke(): Response
    {
        abort_if(Site::hidesLoomFeatures(), 404);

        return Inertia::render('loom-weave-demo');
    }
}
