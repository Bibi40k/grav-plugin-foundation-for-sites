#!/bin/bash

css66="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/css/"
js66="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/js/"

css65="https://cdn.jsdelivr.net/npm/foundation-sites@6.5.3/dist/css/"
js65="https://cdn.jsdelivr.net/npm/foundation-sites@6.5.3/dist/js/"

wget -r -np -k $css65
wget -r -np -k $js65
