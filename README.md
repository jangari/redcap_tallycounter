# REDCap Tally Counter

Converts an integer or number validated text entry field into a tally counter with buttons for incrementing and decrementing the value.

## Installation

### From Module Repository

Install the module from the REDCap module repository and enable in the Control Center, then enable on projects as needed.

### From GitHub

Clone the repository and rename the directory to include a version number, e.g., `tally_counter_v1.0.0`, and copy to your modules directory, then enable in Control Center and on projects as needed.

## Usage

Create a text entry field with either `integer` or `number` validation, and annotate it with `@TALLY.` On surveys and data entry forms, fields are augmented with increment and decrement buttons, which, when clicked or tapped, will increment or decrement the current value of the field.

## Why?

Tally counters are used in several research disciplines. They are used any time the research project needs to reliably count tokens of anything. Traffic studies utilise tally counters to sample the density and variety of traffic flow. Ecologists use tally counters to count occurrences of animals, or otherwise occurrences of evidence of animals such as tracks, droppings, burrows, or the occurrence of foreign objects, such as plastic bags, discarded cigarettes, food waste.

Traditionally, such studies would employ a physical tally counter, or a series of tally counters mounted to a clipboard, which allows a single survey to inspect for perhaps a handful of items at a time.

Using this module, a form can be built using Field Embedding, that can display as many digital tally counters as can feasibly fit on the display of the device being used.

CSS injection (using the REDCap CSS Injector module) can be used to, for example, hide the decrement button so that the tally may only increment, or it can alter the size and colour of the buttons. Buttons are created using classes `tally-counter,` `tally-plus` and `tally-minus` for simple CSS targeting.
