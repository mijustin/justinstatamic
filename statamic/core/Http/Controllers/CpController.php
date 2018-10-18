<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Folder;
use Illuminate\Http\Request;

/**
 * The base control panel controller
 */
class CpController extends Controller
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new CpController
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get all the template names from the current theme
     *
     * @return array
     */
    public function templates()
    {
        $regex = '/\.(html|blade\.php)$/';
        return collect_files(Folder::disk('theme')->getFilesRecursively('templates'))
            ->filterByRegex($regex)
            ->map(function ($path) use ($regex) {
                $parts = explode('/', $path);
                array_shift($parts);
                return preg_replace($regex, '', join('/', $parts));
            })->values()->all();
    }

    public function themes()
    {
        $themes = [];

        foreach (Folder::disk('themes')->getFolders('/') as $folder) {
            $name = $folder;

            // Get the name if one exists in a meta file
            if (File::disk('themes')->exists($folder.'/meta.yaml')) {
                $meta = YAML::parse(File::disk('themes')->get($folder.'/meta.yaml'));
                $name = array_get($meta, 'name', $folder);
            }

            $themes[] = compact('folder', 'name');
        }

        return $themes;
    }
}
