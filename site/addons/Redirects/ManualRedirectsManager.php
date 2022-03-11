<?php

namespace Statamic\Addons\Redirects;

class ManualRedirectsManager extends RedirectsManager
{
    /**
     * Add or update the given redirect, optionally at a specific position.
     *
     * @param ManualRedirect $redirect
     * @param int $position Zero-based position where to insert the redirect.
     *
     * @return ManualRedirectsManager
     */
    public function add(ManualRedirect $redirect, $position = null)
    {
        if ($redirect->getFrom() === $redirect->getTo()) {
            return $this;
        }

        $data = $redirect->toArray();
        unset($data['from']);

        $this->redirects[$redirect->getFrom()] = $data;

        if ($position !== null) {
            $this->setPosition($redirect->getFrom(), $position);
        }

        return $this;
    }

    /**
     * Update the position of an existing redirect given by the route.
     *
     * @param string $route
     * @param int $position
     *
     * @return ManualRedirectsManager
     */
    public function setPosition($route, $position)
    {
        if (!$this->exists($route)) {
            return $this;
        }

        $data = $this->redirects[$route];
        unset($this->redirects[$route]);
        $redirects = [];
        $i = 0;

        foreach ($this->redirects as $redirectRoute => $redirectData) {
            if ($i === $position) {
                $redirects[$route] = $data;
            }
            $redirects[$redirectRoute] = $redirectData;
            $i++;
        }

        if (!isset($redirects[$route])) {
            $redirects[$route] = $data;
        }

        $this->redirects = $redirects;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($route)
    {
        parent::remove($route);

        $this->redirectsLogger->removeManualRedirect($route);

        return $this;
    }

    /**
     * Get a redirect by route.
     *
     * @param string $route
     *
     * @return ManualRedirect
     */
    public function get($route)
    {
        if (!$this->exists($route)) {
            return null;
        }

        $data = $this->redirects[$route];

        return (new ManualRedirect())
            ->setFrom($route)
            ->setTo($data['to'])
            ->setStatusCode($data['status_code'])
            ->setRetainQueryStrings((bool)$data['retain_query_strings'])
            ->setLocale($data['locale'])
            ->setStartDate(isset($data['start_date']) && $data['start_date'] ? new \DateTime($data['start_date']) : null)
            ->setEndDate(isset($data['end_date']) && $data['end_date'] ? new \DateTime($data['end_date']) : null);
    }
}
