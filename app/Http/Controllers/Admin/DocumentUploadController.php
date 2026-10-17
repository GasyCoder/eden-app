<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Téléversement des documents PDF (numéros de revue, journal MaToY…).
 */
class DocumentUploadController extends Controller
{
    public const MAX_KB = 51200;

    public function store(Request $request): JsonResponse
    {
        // Réponse JSON explicite : l'application ne rend le JSON d'erreur que pour /api
        $validator = Validator::make($request->all(), [
            'document' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.self::MAX_KB],
        ], [
            'document.mimes' => 'Seuls les fichiers PDF sont acceptés.',
            'document.mimetypes' => 'Seuls les fichiers PDF sont acceptés.',
            'document.max' => 'Le PDF dépasse 50 Mo.',
            'document.uploaded' => 'Le serveur a refusé le fichier : il dépasse la taille autorisée par PHP (upload_max_filesize).',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $file = $validator->validated()['document'];
        $path = $file->store('documents', 'public');
        $contents = Storage::disk('public')->get($path) ?? '';

        return response()->json([
            'path' => Storage::url($path),
            'size' => $file->getSize(),
            'name' => $file->getClientOriginalName(),
            // Estimation du nombre de pages (objets /Type /Page du PDF)
            'pages' => preg_match_all('/\/Type\s*\/Page(?![s\w])/', $contents) ?: null,
        ]);
    }
}
