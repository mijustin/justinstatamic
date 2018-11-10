<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Folder;
use Statamic\API\GlobalSet;
use Statamic\API\Helper;
use Statamic\API\Str;
use Statamic\API\Fieldset;

class GlobalsController extends CpController
{
    public function index()
    {
        $globals = collect(GlobalSet::all())->filter(function ($global) {
            return me()->can("globals:{$global->slug()}:view");
        });

        if (count($globals) === 1) {
            return redirect()->route('globals.edit', $globals->first()->slug());
        }
        if (count($globals) === 0) {
            return redirect()->route('globals.create');
        }

        return view('globals.index', [
            'title' => t('nav_globals')
        ]);
    }

    public function manage()
    {
        return view('globals.configure', [
            'title' => t('global_sets'),
        ]);
    }

    public function get()
    {
        $globals = GlobalSet::all()->filter(function ($global) {
            return me()->can("globals:{$global->slug()}:view");
        })->supplement('title', function ($global) {
            return $global->title();
        })->toArray();

        return ['columns' => ['title'], 'items' => $globals];
    }

    public function create()
    {
        return view('globals.create', [
            'title' => translate('cp.create_global_set')
        ]);
    }

    public function store()
    {
        $this->access('globals:*:edit');

        $title = $this->request->input('title');

        $slug = ($this->request->has('slug')) ? $this->request->input('slug') : Str::slug($title, '_');

        $this->validate($this->request, [
            'title' => 'required',
            'slug' => 'alpha_dash',
            'fieldset' => 'required'
        ]);

        $global = GlobalSet::create($slug)->with([
            'fieldset' => $this->request->input('fieldset', 'globals'),
            'title' => $title
        ])->get();

        $global->ensureId();

        $global->save();

        return redirect()->route('globals.edit', $slug)->with('success', translate('cp.global_set_created', ['type' => $title]));
    }

    public function delete()
    {
        $ids = Helper::ensureArray($this->request->input('ids'));

        foreach ($ids as $id) {
            GlobalSet::find($id)->delete();
        }

        return ['success' => true];
    }

    public function configure($global)
    {
        $global = GlobalSet::whereHandle($global);

        return view('globals.edit', [
            'global' => $global,
            'title'  => $global->title(),
        ]);
    }

    public function update($global)
    {
        $this->validate($this->request, [
            'title' => 'required',
            'slug' => 'alpha_dash',
            'fieldset' => 'required'
        ]);

        $global = GlobalSet::whereHandle($global);

        $global->title($this->request->input('title'));
        $global->fieldset($this->request->input('fieldset'));

        $global->save();

        return back()->with('success', trans('cp.globals_updated'));
    }
}
