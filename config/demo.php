<?php

/*
 * The public site is also exported as plain HTML for a design review. A static
 * copy cannot answer the catalogue's GET filters, so the export run sets
 * DEMO_NOTICE=1 and the pages say so instead of looking broken.
 */
return [
    'notice' => filter_var(env('DEMO_NOTICE', false), FILTER_VALIDATE_BOOL),
];
