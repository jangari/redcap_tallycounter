# REDCap Tally Counter

This REDCap External Module allows users to create tally counter fields in which the click or tap of a button increments/decrements a tally count by 1.

## Installation

### From Module Repository

Install the module from the REDCap module repository and enable in the Control Center, then enable on projects as needed.

### From GitHub

Clone the repository and rename the directory to include a version number, e.g., `tally_counter_v1.0.0`, and copy to your modules directory, then enable in Control Center and on projects as needed.

## Usage

Create a text entry field with either `integer` or `number` validation, and annotate it with `@TALLY.` On surveys and data entry forms, fields are augmented with decrement and increment buttons, which, when clicked or tapped, will decrement or increment the current value of the field.

## Why?

Ecological studies often follow a design whereby a sample area is defined and marked, and then surveyors inspect the area to count instances of various items. These inspections are repeated on a regular basis, and the numbers of those various items charted over time. Due to the number of items that surveyors might be searching for, it is not feasible to mentally count and then enter the number upon completion of the inspection, which would be required with a traditional REDCap integer field. Such a method would require repeating the inspection per item.

Traditionally, such studies would employ a tally counter, or a series of tally counters mounted to a clipboard, which allows a single survey to inspect for perhaps a handful of items at a time.

Using this module, a form can be built using Field Embedding, that can display as many digital tally counters as can fit on the display of the device being used.

CSS injection (using the REDCap CSS Injector module) can be used to, for example, hide the decrement button so that the tally may only increment, or it can alter the size and colour of the buttons. Buttons are created using classes `tally-counter,` `tally-plus` and `tally-minus` for simple CSS targeting.
