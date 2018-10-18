<?php

namespace Statamic\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Statamic\API\Collection;
use Statamic\API\Entry;
use Statamic\Events\Data\PublishFieldsetFound;

class PublishEntryController extends PublishController
{
    /**
     * Create a new entry.
     *
     * @param  string  $collection
     * @return \Illuminate\Contracts\View\View
     */
    public function create($collection)
    {
        $this->authorize("collections:{$collection}:create");

        /**
         * The redirect can be removed since `authorize` doesn't even let use
         * touch get through validation when the collection doesn't exist in
         * the first place.
         *
         *      $collection = Collection::whereHandle($collection)
         *
         * Leaving this here for edge cases like when we disable authorize on
         * non-existing collections.
         */
        if (! $collection = Collection::whereHandle($collection)) {
            return redirect()->route('collections')->withErrors("Collection [{$collection->path()}] doesn't exist.");
        }

        $fieldset = $collection->fieldset();
        event(new PublishFieldsetFound($fieldset, 'entry'));

        $data = $this->addBlankFields($fieldset);
        $data['slug'] = null; // For Vue. Slug might not've been in the fieldset.

        $extra = [
            'collection' => $collection->path(),
            'order_type' => $collection->order(),
            'route'      => $collection->route()
        ];

        return view('publish', [
            'extra'             => $extra,
            'is_new'            => true,
            'content_data'      => $data,
            'content_type'      => 'entry',
            'fieldset'          => $fieldset->toPublishArray(),
            'title'             => $this->title($fieldset),
            'uuid'              => null,
            'uri'               => null,
            'url'               => null,
            'slug'              => null,
            'status'            => $collection->get('default_status') === 'draft' ? false : true,
            'locale'            => $this->locale(request()),
            'is_default_locale' => true,
            'locales'           => $this->getLocales(),
            'suggestions'        => $this->getSuggestions($fieldset),
        ]);
    }

    /**
     * Edit the entry.
     *
     * @param  string  $collection
     * @param  string  $slug
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($collection, $slug)
    {
        $this->authorize("collections:$collection:view");

        $locale = $this->locale($this->request);

        if (! $entry = Entry::whereSlug($slug, $collection)) {
            return redirect()->route('entries.show', $collection)->withErrors('No entry found.');
        }

        $id = $entry->id();

        $entry = $entry->in($locale)->get();

        $status = $entry->published();

        $extra = [
            'collection'    => $collection,
            'default_slug'  => $entry->slug(),
            'default_order' => $entry->order(),
            'order_type'    => $entry->orderType()
        ];

        $fieldset = $entry->fieldset();
        event(new PublishFieldsetFound($fieldset, 'entry', $entry));

        $data = $this->addBlankFields($fieldset, $entry->processedData());
        $data['slug'] = $entry->slug();

        if ($entry->orderType() === 'date') {
            // Get the datetime without milliseconds
            $datetime = substr($entry->date()->toDateTimeString(), 0, 16);
            // Then strip off the time, if it's not supposed to be there.
            $datetime = ($entry->hasTime()) ? $datetime : substr($datetime, 0, 10);

            $data['date'] = $datetime;
        }

        return view('publish', [
            'extra'              => $extra,
            'is_new'             => false,
            'content_data'       => $data,
            'content_type'       => 'entry',
            'fieldset'           => $fieldset->toPublishArray(),
            'title'              => array_get($data, 'title', $slug),
            'uuid'               => $id,
            'uri'                => $entry->uri(),
            'url'                => $entry->url(),
            'slug'               => $slug,
            'status'             => $status,
            'locale'             => $locale,
            'is_default_locale'  => $entry->isDefaultLocale(),
            'locales'            => $this->getLocales($id),
            'suggestions'        => $this->getSuggestions($fieldset),
        ]);
    }

    /**
     * Build the redirect.
     *
     * @param  Request  $request
     * @param  \Statamic\Contracts\Data\Entries\Entry  $entry
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirect(Request $request, $entry)
    {
        if ($request->another) {
            return route('entry.create', $entry->collectionName());
        }

        if ($request->continue) {
            return route('entry.edit', [
                'collection' => $entry->collectionName(),
                'slug'       => $entry->slug(),
            ]);
        }

        return route('entries.show', $entry->collectionName());
    }

    /**
     * Create the title for the page.
     *
     * @param  \Statamic\CP\Fieldset  $fieldset
     * @return string
     */
    private function title(\Statamic\CP\Fieldset $fieldset)
    {
        if (! $title = array_get($fieldset->contents(), 'create_title')) {
            return translate('cp.create_entry', [
                'noun' => $fieldset->title()
            ]);
        }

        return $title;
    }

    /**
     * Whether the user is authorized to publish the object.
     *
     * @param Request $request
     * @return bool
     */
    protected function canPublish(Request $request)
    {
        $collection = $request->input('extra.collection');

        return $request->user()->can(
            $request->new ? "collections:$collection:create" : "collections:$collection:edit"
        );
    }

    /**
     * Return the locale from the request.
     *
     * @param  Request  $request
     * @return string
     */
    private function locale(Request $request)
    {
        return $request->query('locale', site_locale());
    }
}
