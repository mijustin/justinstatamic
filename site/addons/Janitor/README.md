# Bardify
> Convert your Statamic Replicator and/or content fields into Bard fields.

## Overview
Bard fields are just like Replicator fields, except that the "text" blocks are implied and saved as `text` in your content.

For example you may have a Replicator field in your fieldset where you use your "text" set like this:

``` yaml
fields:
  story:
    type: replicator
    sets:
      content:  # The "text" set. It's the one with just a single text based field.
        fields:
          html:
            type: redactor
      quote:
        fields:
          quote:
            type: text
          cite:
            type: text
```

and your data would be saved like this:

``` yaml
story:
  -
    type: text
    text: "<p>This is my story</p>"
  -
    type: quote
    quote: Oh Hai Mark
    cite: Tommy Wiseau
```

To change to Bard, you'd need to replace your `content`/`html` set to `text`/`text`.

``` yaml
story:
  -
    type: text
    text: "<p>This is my story</p>"
  -
    type: quote
    quote: Oh Hai Mark
    cite: Tommy Wiseau
```

Simple enough, but tedious to do it for all your entries.

**If you happened to already call your text set `text` with a field named `text` - you're in luck and don't need to change anything.**

## Usage

> Note: This modifies your data, so consider making a backup first.

> Another Note: This doesn't support multiple locales yet.

Download this repo and place it in `site/addons/Bardify` and run this command:

```
php please bardify
```

It will automate the following:

- Go through any relevant content files and change the types/fields.
- Remove the "text" set from the Replicator field.
- Change `type: replicator` to `type: bard`. (Wow!)
- If you were using a markdown fieldtype for the text field, it will add `markdown: true` to the Bard field.


## Converting content fields to Bard

You may also convert your `content` field (the text below your YAML front-matter) into a Bard field by running the following command:

```
php please bardify:content
```

It will automate the following:
- Go through any relevant content files and replace the content text with the field name of your choice.
- If you were using a markdown fieldtype, it will add `markdown: true` to the Bard field.