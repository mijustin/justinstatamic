<?php

return [
    'from' => 'Source URL',
    'from_instructions' => 'Enter a relative URL, e.g. `/source`.',
    'target_type' => 'Target Type',
    'to_url' => 'URL',
    'to_page' => 'Page',
    'to_entry' => 'Entry',
    'to_term' => 'Term',
    'status_code' => 'Status Code',
    'locale' => 'Locale',
    'retain_query_strings' => 'Retain query strings',
    'retain_query_strings_instructions' => 'Query strings from the source URL are appended to the redirect target URL.',
    'locale_instructions' => 'Optionally restrict the redirect to a locale.',
    'timed_activation' => 'Timed Activation',
    'timed_activation_instructions' => 'Activate to apply this redirect during a limited period of time. Only specifying a start date delays the activation of the redirect after the given date. Only specifying an end date activates the redirect until the given date. If both dates are specified, a temporary redirect status code (302) gets applied automatically.',
    'start_date' => 'Start Date/Time',
    'end_date' => 'End Date/Time',
];
