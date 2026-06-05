<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ContentRepository;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(ContentRepository $contentRepository): Response
    {
        return Inertia::render('Home', [
            'blocks' => $contentRepository->blocks(),
            'collections' => $contentRepository->collections(),
            'canEdit' => (bool) request()->user()?->is_admin,
        ]);
    }
}
