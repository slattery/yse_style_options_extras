# YSE Style Options Extras

Adding decorators and olugins to the style_options paragraph behavior module.

## Adding per-paragraph options in the paragraph edit form

This module decorates the discovery service to add paragraph type configuration
storage to the YAML file-based discovery process. We replace the style_options
Behavior class via a hook_paragraph_type_info_alter() implementation, to
override the ConfigureForm and add a yaml editor to the form.

The decorators allow for the configuration to be stored in the paragraph type
storage, and a YAML textarea is added to the paragraph type form to hold
per-paragraph type configuration.

There is also a YAML Discovery Decorator that looks through paragraph type
configuration and adds those structures to the default YAML file-based discovery
in themes and modules.

## Plugins

### yse_data_attribute

This plugin allows for the creation of CSS variables that will be attached to
the '#attributes' property of a paragraph. As expected, these are simple keys,
which will overwrite existing values.

#### Config keywords

- 'options' - same as the style_options paragraph behavior
- 'default' - same as the style_options paragraph behavior
- 'method' - used in switch to process input from the form
- 'attribute' - string for the attribute to set, defaults to option_id
- 'format' - if found, passes value through sprintf before output
- 'label' - Used in configuration form.

#### Methods

This plugin adds a 'method' keyword to config, and the methods work as follows:

- 'set_attribute' - assigns value for the 'attribute'. 'format' will be applied.
- 'set_boolean_true' - true: attribute with no value if true.  false: no output.
- 'set_boolean_string' - assigns either "true" of "false" to attribute.
- 'set_textentry' - uses a textarea to allow arbitrary key/value pairs to be set.

The 'set_attribute' method is the default, and wants to find the 'options' list.
If 'set_boolean_true' or 'set_boolean_string' are used, the 'options' list is
not needed. If options are found for 'true' and/or 'false' for
'set_boolean_string', the labels will be used. The 'set_textentry' method
expects lines in the format of key|value.

### yse_inline_pair

This plugin allows for key value pairs to be set inline in the 'style'
attribute. Keys can be CSS properties or CSS variables. Values can be CSS rules
assignments or CSS variables, with or without fallback, which in turn maybe a
rules assignment or a CSS variable.

#### Config keywords

- 'options' - same as the style_options paragraph behavior
- 'default' - same as the style_options paragraph behavior
- 'property' - used as the CSS property or the CSS variable name
- 'fallback' - used as the fallback value for CSS variables
- 'format' - if found, passes value through sprintf before output
- 'label' - Used in configuration form.

The 'property' is identified as a CSS property or CSS variable name if the
property has two leading dashes '--'. the value chosen from the 'options' list
is used as the value for the CSS property or CSS variable. The value itself may
be a CSS variable. When a CSS variable is used as a value, it is wrapped with
'var()' The dashes may be prefixed to the value using the 'format' keyword. The
'format' can contain any valid sprintf() format string that would accommoate the
option value. The 'fallback' keyword is only used when a CSS variable is present
as the value. The 'default' is still used in relation to the options list.

Each time this plugin is used, it merges with the previous plugin's pairs, later
pairs will override earlier pairs if keys match.

### yse_css_class

This plugin operates as the original style_options paragraph behavior, with the
addition of a special case for 'nullify' which builds a class name for twig
macros downstream.
