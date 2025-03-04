<?php

use SocialiteUi\Tests\OrchestraTestCase;

pest()->printer()->compact();

uses(OrchestraTestCase::class)->in('Feature', 'Unit');
